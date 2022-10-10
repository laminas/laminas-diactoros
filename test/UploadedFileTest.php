<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros;

use InvalidArgumentException;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\UploadedFile;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use RuntimeException;

use function basename;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function fopen;
use function is_string;
use function sys_get_temp_dir;
use function tempnam;
use function uniqid;
use function unlink;

use const UPLOAD_ERR_CANT_WRITE;
use const UPLOAD_ERR_EXTENSION;
use const UPLOAD_ERR_FORM_SIZE;
use const UPLOAD_ERR_INI_SIZE;
use const UPLOAD_ERR_NO_FILE;
use const UPLOAD_ERR_NO_TMP_DIR;
use const UPLOAD_ERR_OK;
use const UPLOAD_ERR_PARTIAL;

final class UploadedFileTest extends TestCase
{
    /** @var false|null|string */
    private $orgFile;

    /** @var mixed */
    private $tmpFile;

    protected function setUp(): void
    {
        $this->tmpFile = null;
        $this->orgFile = null;
    }

    protected function tearDown(): void
    {
        if (is_string($this->tmpFile) && file_exists($this->tmpFile)) {
            unlink($this->tmpFile);
        }

        if (is_string($this->orgFile) && file_exists($this->orgFile)) {
            unlink($this->orgFile);
        }
    }

    /** @return non-empty-array<non-empty-string, array{mixed}> */
    public function invalidStreams(): array
    {
        return [
            'null'  => [null],
            'true'  => [true],
            'false' => [false],
            'int'   => [1],
            'float' => [1.1],
            /* Have not figured out a valid way to test an invalid path yet; null byte injection
             * appears to get caught by fopen()
            'invalid-path' => [ ('WIN' === strtoupper(substr(PHP_OS, 0, 3))) ? '[:]' : 'foo' . chr(0) ],
             */
            'array'  => [['filename']],
            'object' => [(object) ['filename']],
        ];
    }

    /**
     * @dataProvider invalidStreams
     */
    public function testRaisesExceptionOnInvalidStreamOrFile(mixed $streamOrFile): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UploadedFile($streamOrFile, 0, UPLOAD_ERR_OK);
    }

    public function testValidSize(): void
    {
        $uploaded = new UploadedFile(fopen('php://temp', 'wb+'), 123, UPLOAD_ERR_OK);

        $this->assertSame(123, $uploaded->getSize());
    }

    /** @return non-empty-array<non-empty-string, array{int}> */
    public function invalidErrorStatuses(): array
    {
        return [
            'negative' => [-1],
            'too-big'  => [9],
        ];
    }

    /**
     * @dataProvider invalidErrorStatuses
     */
    public function testRaisesExceptionOnInvalidErrorStatus(int $status): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('status');

        new UploadedFile(fopen('php://temp', 'wb+'), 0, $status);
    }

    public function testValidClientFilename(): void
    {
        $file = new UploadedFile(fopen('php://temp', 'wb+'), 0, UPLOAD_ERR_OK, 'boo.txt');
        $this->assertSame('boo.txt', $file->getClientFilename());
    }

    public function testValidNullClientFilename(): void
    {
        $file = new UploadedFile(fopen('php://temp', 'wb+'), 0, UPLOAD_ERR_OK, null);
        $this->assertSame(null, $file->getClientFilename());
    }

    public function testValidClientMediaType(): void
    {
        $file = new UploadedFile(fopen('php://temp', 'wb+'), 0, UPLOAD_ERR_OK, 'foobar.baz', 'mediatype');
        $this->assertSame('mediatype', $file->getClientMediaType());
    }

    public function testGetStreamReturnsOriginalStreamObject(): void
    {
        $stream = new Stream('php://temp');
        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);
        $this->assertSame($stream, $upload->getStream());
    }

    public function testGetStreamReturnsWrappedPhpStream(): void
    {
        $stream       = fopen('php://temp', 'wb+');
        $upload       = new UploadedFile($stream, 0, UPLOAD_ERR_OK);
        $uploadStream = $upload->getStream()->detach();
        $this->assertSame($stream, $uploadStream);
    }

    public function testGetStreamReturnsStreamForFile(): void
    {
        $this->tmpFile = $stream = tempnam(sys_get_temp_dir(), 'diac');
        $upload        = new UploadedFile($stream, 0, UPLOAD_ERR_OK);
        $uploadStream  = $upload->getStream();
        $r             = new ReflectionProperty($uploadStream, 'stream');
        $r->setAccessible(true);
        $this->assertSame($stream, $r->getValue($uploadStream));
    }

    public function testMovesFileToDesignatedPath(): void
    {
        $originalContents = 'Foo bar!';
        $stream           = new Stream('php://temp', 'wb+');
        $stream->write($originalContents);
        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);

        $this->tmpFile = $to = tempnam(sys_get_temp_dir(), 'diac');
        $upload->moveTo($to);
        $this->assertTrue(file_exists($to));
        $contents = file_get_contents($to);
        $this->assertSame($originalContents, $contents);
    }

    /** @return non-empty-array<non-empty-string, array{mixed}> */
    public function invalidMovePaths(): array
    {
        return [
            'null'   => [null],
            'true'   => [true],
            'false'  => [false],
            'int'    => [1],
            'float'  => [1.1],
            'empty'  => [''],
            'array'  => [['filename']],
            'object' => [(object) ['filename']],
        ];
    }

    /**
     * @dataProvider invalidMovePaths
     */
    public function testMoveRaisesExceptionForInvalidPath(mixed $path): void
    {
        $stream = new Stream('php://temp', 'wb+');
        $stream->write('Foo bar!');
        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);

        $this->tmpFile = $path;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('path');

        $upload->moveTo($path);
    }

    public function testMoveCannotBeCalledMoreThanOnce(): void
    {
        $stream = new Stream('php://temp', 'wb+');
        $stream->write('Foo bar!');
        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);

        $this->tmpFile = $to = tempnam(sys_get_temp_dir(), 'diac');
        $upload->moveTo($to);
        $this->assertTrue(file_exists($to));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('moved');

        $upload->moveTo($to);
    }

    public function testCannotRetrieveStreamAfterMove(): void
    {
        $stream = new Stream('php://temp', 'wb+');
        $stream->write('Foo bar!');
        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);

        $this->tmpFile = $to = tempnam(sys_get_temp_dir(), 'diac');
        $upload->moveTo($to);
        $this->assertTrue(file_exists($to));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('moved');

        $upload->getStream();
    }

    /** @return non-empty-array<non-empty-string, array{positive-int}> */
    public function nonOkErrorStatus(): array
    {
        return [
            'UPLOAD_ERR_INI_SIZE'   => [UPLOAD_ERR_INI_SIZE],
            'UPLOAD_ERR_FORM_SIZE'  => [UPLOAD_ERR_FORM_SIZE],
            'UPLOAD_ERR_PARTIAL'    => [UPLOAD_ERR_PARTIAL],
            'UPLOAD_ERR_NO_FILE'    => [UPLOAD_ERR_NO_FILE],
            'UPLOAD_ERR_NO_TMP_DIR' => [UPLOAD_ERR_NO_TMP_DIR],
            'UPLOAD_ERR_CANT_WRITE' => [UPLOAD_ERR_CANT_WRITE],
            'UPLOAD_ERR_EXTENSION'  => [UPLOAD_ERR_EXTENSION],
        ];
    }

    /**
     * @dataProvider nonOkErrorStatus
     * @group 60
     */
    public function testConstructorDoesNotRaiseExceptionForInvalidStreamWhenErrorStatusPresent(int $status): void
    {
        $uploadedFile = new UploadedFile('not ok', 0, $status);
        $this->assertSame($status, $uploadedFile->getError());
    }

    /**
     * @dataProvider nonOkErrorStatus
     * @group 60
     */
    public function testMoveToRaisesExceptionWhenErrorStatusPresent(int $status): void
    {
        $uploadedFile = new UploadedFile('not ok', 0, $status);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('upload error');

        $uploadedFile->moveTo(__DIR__ . '/' . uniqid());
    }

    /**
     * @dataProvider nonOkErrorStatus
     * @group 60
     */
    public function testGetStreamRaisesExceptionWhenErrorStatusPresent(int $status): void
    {
        $uploadedFile = new UploadedFile('not ok', 0, $status);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('upload error');

        $uploadedFile->getStream();
    }

    /**
     * @group 82
     */
    public function testMoveToCreatesStreamIfOnlyAFilenameWasProvided(): void
    {
        $this->orgFile = tempnam(sys_get_temp_dir(), 'ORG');
        $this->tmpFile = tempnam(sys_get_temp_dir(), 'DIA');
        file_put_contents($this->orgFile, 'Hello');

        $original = file_get_contents($this->orgFile);

        $uploadedFile = new UploadedFile($this->orgFile, 100, UPLOAD_ERR_OK, basename($this->orgFile), 'text/plain');
        $uploadedFile->moveTo($this->tmpFile);

        $contents = file_get_contents($this->tmpFile);

        $this->assertSame($original, $contents);
    }

    /** @return iterable<int, array{int, non-empty-string}> */
    public function errorConstantsAndMessages(): iterable
    {
        foreach (UploadedFile::ERROR_MESSAGES as $constant => $message) {
            if ($constant === UPLOAD_ERR_OK) {
                continue;
            }
            yield $constant => [$constant, $message];
        }
    }

    /** @dataProvider errorConstantsAndMessages */
    public function testGetStreamRaisesExceptionWithAppropriateMessageWhenUploadErrorDetected(
        int $constant,
        string $message
    ): void {
        $uploadedFile = new UploadedFile(__FILE__, 100, $constant);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($message);
        $uploadedFile->getStream();
    }

    /**
     * @dataProvider errorConstantsAndMessages
     */
    public function testMoveToRaisesExceptionWithAppropriateMessageWhenUploadErrorDetected(
        int $constant,
        string $message
    ): void {
        $uploadedFile = new UploadedFile(__FILE__, 100, $constant);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($message);
        $uploadedFile->moveTo('/tmp/foo');
    }

    public function testMoveToInCLIShouldRemoveOriginalFile(): void
    {
        $this->orgFile = tempnam(sys_get_temp_dir(), 'ORG');
        file_put_contents($this->orgFile, 'Hello');
        $upload = new UploadedFile($this->orgFile, 0, UPLOAD_ERR_OK);

        $this->tmpFile = $to = tempnam(sys_get_temp_dir(), 'diac');
        $upload->moveTo($to);
        $this->assertFalse(file_exists($this->orgFile));
        $this->assertTrue(file_exists($to));
    }
}
