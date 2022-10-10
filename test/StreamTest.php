<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros;

use CurlHandle;
use GdImage;
use InvalidArgumentException;
use Laminas\Diactoros\Stream;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use RuntimeException;

use function curl_init;
use function feof;
use function file_exists;
use function file_put_contents;
use function fopen;
use function fread;
use function fseek;
use function fstat;
use function ftok;
use function function_exists;
use function fwrite;
use function imagecreate;
use function is_resource;
use function shmop_open;
use function stream_get_meta_data;
use function sys_get_temp_dir;
use function tempnam;
use function uniqid;
use function unlink;

use const DIRECTORY_SEPARATOR;

final class StreamTest extends TestCase
{
    /** @var string|null|false */
    private $tmpnam;

    private Stream $stream;

    protected function setUp(): void
    {
        $this->tmpnam = null;
        $this->stream = new Stream('php://memory', 'wb+');
    }

    protected function tearDown(): void
    {
        if ($this->tmpnam && file_exists($this->tmpnam)) {
            unlink($this->tmpnam);
        }
    }

    public function testCanInstantiateWithStreamIdentifier(): void
    {
        $this->assertInstanceOf(Stream::class, $this->stream);
    }

    public function testCanInstantiateWithStreamResource(): void
    {
        $resource = fopen('php://memory', 'wb+');
        $stream   = new Stream($resource);
        $this->assertInstanceOf(Stream::class, $stream);
    }

    public function testCanInstantiateWithGDResource(): void
    {
        $resource = imagecreate(1, 1);
        self::assertInstanceOf(GdImage::class, $resource);
        $stream = new Stream($resource);
        $this->assertInstanceOf(Stream::class, $stream);
    }

    public function testIsReadableReturnsFalseIfStreamIsNotReadable(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        $stream       = new Stream($this->tmpnam, 'w');
        $this->assertFalse($stream->isReadable());
    }

    public function testIsWritableReturnsFalseIfStreamIsNotWritable(): void
    {
        $stream = new Stream('php://memory', 'r');
        $this->assertFalse($stream->isWritable());
    }

    public function testToStringRetrievesFullContentsOfStream(): void
    {
        $message = 'foo bar';
        $this->stream->write($message);
        $this->assertSame($message, (string) $this->stream);
    }

    public function testDetachReturnsResource(): void
    {
        $resource = fopen('php://memory', 'wb+');
        $stream   = new Stream($resource);
        $this->assertSame($resource, $stream->detach());
    }

    public function testPassingInvalidStreamResourceToConstructorRaisesException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        /** @psalm-suppress InvalidArgument */
        new Stream(['  THIS WILL NOT WORK  ']);
    }

    public function testStringSerializationReturnsEmptyStringWhenStreamIsNotReadable(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $stream = new Stream($this->tmpnam, 'w');

        $this->assertSame('', $stream->__toString());
    }

    public function testCloseClosesResource(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        $resource     = fopen($this->tmpnam, 'wb+');
        $stream       = new Stream($resource);
        $stream->close();
        $this->assertFalse(is_resource($resource));
    }

    public function testCloseUnsetsResource(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        $resource     = fopen($this->tmpnam, 'wb+');
        $stream       = new Stream($resource);
        $stream->close();

        $this->assertNull($stream->detach());
    }

    public function testCloseDoesNothingAfterDetach(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        $resource     = fopen($this->tmpnam, 'wb+');
        $stream       = new Stream($resource);
        $detached     = $stream->detach();

        $stream->close();
        $this->assertTrue(is_resource($detached));
        $this->assertSame($resource, $detached);
    }

    /**
     * @group 42
     */
    public function testSizeReportsNullWhenNoResourcePresent(): void
    {
        $this->stream->detach();
        $this->assertNull($this->stream->getSize());
    }

    public function testTellReportsCurrentPositionInResource(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream   = new Stream($resource);

        fseek($resource, 2);

        $this->assertSame(2, $stream->tell());
    }

    public function testTellRaisesExceptionIfResourceIsDetached(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream   = new Stream($resource);

        fseek($resource, 2);
        $stream->detach();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No resource');

        $stream->tell();
    }

    public function testEofReportsFalseWhenNotAtEndOfStream(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream   = new Stream($resource);

        fseek($resource, 2);
        $this->assertFalse($stream->eof());
    }

    public function testEofReportsTrueWhenAtEndOfStream(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream   = new Stream($resource);

        while (! feof($resource)) {
            fread($resource, 4096);
        }
        $this->assertTrue($stream->eof());
    }

    public function testEofReportsTrueWhenStreamIsDetached(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream   = new Stream($resource);

        fseek($resource, 2);
        $stream->detach();
        $this->assertTrue($stream->eof());
    }

    public function testIsSeekableReturnsTrueForReadableStreams(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream   = new Stream($resource);
        $this->assertTrue($stream->isSeekable());
    }

    public function testIsSeekableReturnsFalseForDetachedStreams(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream   = new Stream($resource);
        $stream->detach();
        $this->assertFalse($stream->isSeekable());
    }

    public function testSeekAdvancesToGivenOffsetOfStream(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream   = new Stream($resource);
        $this->assertNull($stream->seek(2));
        $this->assertSame(2, $stream->tell());
    }

    public function testRewindResetsToStartOfStream(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream   = new Stream($resource);
        $this->assertNull($stream->seek(2));
        $stream->rewind();
        $this->assertSame(0, $stream->tell());
    }

    public function testSeekRaisesExceptionWhenStreamIsDetached(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream   = new Stream($resource);
        $stream->detach();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No resource');

        $stream->seek(2);
    }

    public function testIsWritableReturnsFalseWhenStreamIsDetached(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream   = new Stream($resource);
        $stream->detach();
        $this->assertFalse($stream->isWritable());
    }

    public function testIsWritableReturnsTrueForWritableMemoryStream(): void
    {
        $stream = new Stream("php://temp", "r+b");
        $this->assertTrue($stream->isWritable());
    }

    /** @return non-empty-list<array{non-empty-string, bool, bool}> */
    public function provideDataForIsWritable(): array
    {
        return [
            ['a',   true,  true],
            ['a+',  true,  true],
            ['a+b', true,  true],
            ['ab',  true,  true],
            ['c',   true,  true],
            ['c+',  true,  true],
            ['c+b', true,  true],
            ['cb',  true,  true],
            ['r',   true,  false],
            ['r+',  true,  true],
            ['r+b', true,  true],
            ['rb',  true,  false],
            ['rw',  true,  true],
            ['w',   true,  true],
            ['w+',  true,  true],
            ['w+b', true,  true],
            ['wb',  true,  true],
            ['x',   false, true],
            ['x+',  false, true],
            ['x+b', false, true],
            ['xb',  false, true],
        ];
    }

    private function findNonExistentTempName(): string
    {
        do {
            $tmpnam = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'diac' . uniqid();
        } while (file_exists(sys_get_temp_dir() . $tmpnam));

        return $tmpnam;
    }

    /**
     * @dataProvider provideDataForIsWritable
     * @param non-empty-string $mode
     */
    public function testIsWritableReturnsCorrectFlagForMode(string $mode, bool $fileShouldExist, bool $flag): void
    {
        if ($fileShouldExist) {
            $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
            file_put_contents($this->tmpnam, 'FOO BAR');
        } else {
            // "x" modes REQUIRE that file doesn't exist, so we need to find random file name
            $this->tmpnam = $this->findNonExistentTempName();
        }
        $resource = fopen($this->tmpnam, $mode);
        $stream   = new Stream($resource);
        $this->assertSame($flag, $stream->isWritable());
    }

    /** @return non-empty-list<array{non-empty-string, bool, bool}> */
    public function provideDataForIsReadable(): array
    {
        return [
            ['a',   true,  false],
            ['a+',  true,  true],
            ['a+b', true,  true],
            ['ab',  true,  false],
            ['c',   true,  false],
            ['c+',  true,  true],
            ['c+b', true,  true],
            ['cb',  true,  false],
            ['r',   true,  true],
            ['r+',  true,  true],
            ['r+b', true,  true],
            ['rb',  true,  true],
            ['rw',  true,  true],
            ['w',   true,  false],
            ['w+',  true,  true],
            ['w+b', true,  true],
            ['wb',  true,  false],
            ['x',   false, false],
            ['x+',  false, true],
            ['x+b', false, true],
            ['xb',  false, false],
        ];
    }

    /**
     * @dataProvider provideDataForIsReadable
     * @param non-empty-string $mode
     */
    public function testIsReadableReturnsCorrectFlagForMode(string $mode, bool $fileShouldExist, bool $flag): void
    {
        if ($fileShouldExist) {
            $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
            file_put_contents($this->tmpnam, 'FOO BAR');
        } else {
            // "x" modes REQUIRE that file doesn't exist, so we need to find random file name
            $this->tmpnam = $this->findNonExistentTempName();
        }
        $resource = fopen($this->tmpnam, $mode);
        $stream   = new Stream($resource);
        $this->assertSame($flag, $stream->isReadable());
    }

    public function testWriteRaisesExceptionWhenStreamIsDetached(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream   = new Stream($resource);
        $stream->detach();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No resource');

        $stream->write('bar');
    }

    public function testWriteRaisesExceptionWhenStreamIsNotWritable(): void
    {
        $stream = new Stream('php://memory', 'r');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not writable');

        $stream->write('bar');
    }

    public function testIsReadableReturnsFalseWhenStreamIsDetached(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream   = new Stream($resource);
        $stream->detach();

        $this->assertFalse($stream->isReadable());
    }

    public function testReadRaisesExceptionWhenStreamIsDetached(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'r');
        $stream   = new Stream($resource);
        $stream->detach();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No resource');

        $stream->read(4096);
    }

    public function testReadReturnsEmptyStringWhenAtEndOfFile(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'r');
        $stream   = new Stream($resource);
        while (! feof($resource)) {
            fread($resource, 4096);
        }
        $this->assertSame('', $stream->read(4096));
    }

    public function testGetContentsRisesExceptionIfStreamIsNotReadable(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'w');
        $stream   = new Stream($resource);

        $this->expectException(RuntimeException::class);

        $stream->getContents();
    }

    /** @return non-empty-array<non-empty-string, array{mixed}> */
    public function invalidResources(): array
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        return [
            'null'   => [null],
            'false'  => [false],
            'true'   => [true],
            'int'    => [1],
            'float'  => [1.1],
            'array'  => [[fopen($this->tmpnam, 'r+')]],
            'object' => [(object) ['resource' => fopen($this->tmpnam, 'r+')]],
        ];
    }

    /**
     * @dataProvider invalidResources
     */
    public function testAttachWithNonStringNonResourceRaisesException(mixed $resource): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid stream');

        /** @psalm-suppress MixedArgument */
        $this->stream->attach($resource);
    }

    public function testAttachWithInvalidStringResourceRaisesException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid stream');

        $this->stream->attach('foo-bar-baz');
    }

    public function testAttachWithResourceAttachesResource(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        $resource     = fopen($this->tmpnam, 'r+');
        $this->stream->attach($resource);

        $r = new ReflectionProperty($this->stream, 'resource');
        $r->setAccessible(true);
        $test = $r->getValue($this->stream);
        $this->assertSame($resource, $test);
    }

    public function testAttachWithStringRepresentingResourceCreatesAndAttachesResource(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        $this->stream->attach($this->tmpnam);

        $resource = fopen($this->tmpnam, 'r+');
        fwrite($resource, 'FooBar');

        $this->stream->rewind();
        $test = (string) $this->stream;
        $this->assertSame('FooBar', $test);
    }

    public function testGetContentsShouldGetFullStreamContents(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        $resource     = fopen($this->tmpnam, 'r+');
        $this->stream->attach($resource);

        fwrite($resource, 'FooBar');

        // rewind, because current pointer is at end of stream!
        $this->stream->rewind();
        $test = $this->stream->getContents();
        $this->assertSame('FooBar', $test);
    }

    public function testGetContentsShouldReturnStreamContentsFromCurrentPointer(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        $resource     = fopen($this->tmpnam, 'r+');
        $this->stream->attach($resource);

        fwrite($resource, 'FooBar');

        // seek to position 3
        $this->stream->seek(3);
        $test = $this->stream->getContents();
        $this->assertSame('Bar', $test);
    }

    public function testGetMetadataReturnsAllMetadataWhenNoKeyPresent(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        $resource     = fopen($this->tmpnam, 'r+');
        $this->stream->attach($resource);

        $expected = stream_get_meta_data($resource);
        $test     = $this->stream->getMetadata();

        $this->assertSame($expected, $test);
    }

    public function testGetMetadataReturnsDataForSpecifiedKey(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        $resource     = fopen($this->tmpnam, 'r+');
        $this->stream->attach($resource);

        $metadata = stream_get_meta_data($resource);
        $expected = $metadata['uri'];

        $test = $this->stream->getMetadata('uri');

        $this->assertSame($expected, $test);
    }

    public function testGetMetadataReturnsNullIfNoDataExistsForKey(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        $resource     = fopen($this->tmpnam, 'r+');
        $this->stream->attach($resource);

        $this->assertNull($this->stream->getMetadata('TOTALLY_MADE_UP'));
    }

    /**
     * @group 42
     */
    public function testGetSizeReturnsStreamSize(): void
    {
        $resource = fopen(__FILE__, 'r');
        $expected = fstat($resource);
        $stream   = new Stream($resource);
        $this->assertSame($expected['size'], $stream->getSize());
    }

    /**
     * @group 67
     */
    public function testRaisesExceptionOnConstructionForNonStreamResources(): void
    {
        $resource = $this->getResourceFor67();
        if (false === $resource) {
            $this->markTestSkipped('No acceptable resource available to test ' . __METHOD__);
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('stream');

        /** @psalm-suppress ImplicitToStringCast, PossiblyInvalidArgument */
        new Stream($resource);
    }

    /**
     * @group 67
     */
    public function testRaisesExceptionOnAttachForNonStreamResources(): void
    {
        $resource = $this->getResourceFor67();
        if (false === $resource) {
            $this->markTestSkipped('No acceptable resource available to test ' . __METHOD__);
        }

        $stream = new Stream(__FILE__);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('stream');

        /** @psalm-suppress ImplicitToStringCast, PossiblyInvalidArgument */
        $stream->attach($resource);
    }

    /** @return CurlHandle|GdImage|false|resource */
    public function getResourceFor67()
    {
        if (function_exists('curl_init')) {
            return curl_init();
        }

        if (function_exists('shmop_open')) {
            return shmop_open(ftok(__FILE__, 't'), 'c', 0644, 100);
        }

        if (function_exists('imagecreate')) {
            return imagecreate(200, 200);
        }

        return false;
    }

    public function testCanReadContentFromNotSeekableResource(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'r');
        $stream   = $this
            ->getMockBuilder(Stream::class)
            ->setConstructorArgs([$resource])
            ->setMethods(['isSeekable'])
            ->getMock();

        $stream->expects($this->any())->method('isSeekable')
            ->will($this->returnValue(false));

        $this->assertSame('FOO BAR', $stream->__toString());
    }

    /**
     * @group 42
     */
    public function testSizeReportsNullForPhpInputStreams(): void
    {
        $resource = fopen('php://input', 'r');
        $stream   = new Stream($resource);
        $this->assertNull($stream->getSize());
    }
}
