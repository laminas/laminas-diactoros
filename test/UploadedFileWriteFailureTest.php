<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros;

use Laminas\Diactoros\Exception\UploadedFileErrorException;
use Laminas\Diactoros\UploadedFile;
use LaminasTest\Diactoros\TestAsset\FailingWriteStreamWrapper;
use Override;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function file_exists;
use function file_put_contents;
use function in_array;
use function stream_get_wrappers;
use function stream_wrapper_register;
use function stream_wrapper_unregister;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

use const UPLOAD_ERR_OK;

/**
 * These tests run under the CLI SAPI, so moveTo() takes the non-SAPI branch
 * (stream copy + unlink) rather than move_uploaded_file().
 */
final class UploadedFileWriteFailureTest extends TestCase
{
    private string $source;

    #[Override]
    protected function setUp(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'diac');
        if ($tmp === false) {
            throw new RuntimeException('tempnam failed');
        }
        $this->source = $tmp;
        file_put_contents($this->source, 'uploaded-content');

        if (! in_array(FailingWriteStreamWrapper::SCHEME, stream_get_wrappers(), true)) {
            stream_wrapper_register(FailingWriteStreamWrapper::SCHEME, FailingWriteStreamWrapper::class);
        }
    }

    #[Override]
    protected function tearDown(): void
    {
        if (file_exists($this->source)) {
            unlink($this->source);
        }
        if (in_array(FailingWriteStreamWrapper::SCHEME, stream_get_wrappers(), true)) {
            /** @psalm-suppress UnusedFunctionCall */
            stream_wrapper_unregister(FailingWriteStreamWrapper::SCHEME);
        }
    }

    /**
     * If the target accepts no bytes (disk full, quota, I/O error), moveTo() must not
     * report success, delete the source file and mark the upload as moved.
     */
    public function testMoveToReportsFailedWriteInsteadOfDeletingSource(): void
    {
        $upload = new UploadedFile($this->source, 16, UPLOAD_ERR_OK);
        $target = FailingWriteStreamWrapper::SCHEME . '://dir/target';

        $threw = false;
        try {
            @$upload->moveTo($target);
        } catch (UploadedFileErrorException | RuntimeException) {
            $threw = true;
        }

        $this->assertTrue(
            $threw || file_exists($this->source),
            'moveTo() reported success although nothing was written, and deleted the source file'
        );
    }
}
