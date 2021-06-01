<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros;

use Laminas\Diactoros\PhpInputStream;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function substr;

class PhpInputStreamTest extends TestCase
{
    /**
     * @var string
     */
    protected $file;

    /**
     * @var PhpInputStream
     */
    protected $stream;

    protected function setUp() : void
    {
        $this->file = __DIR__ . '/TestAsset/php-input-stream.txt';
        $this->stream = new PhpInputStream($this->file);
    }

    public function getFileContents()
    {
        return file_get_contents($this->file);
    }

    public function assertStreamContents($test, $message = '')
    {
        $content = $this->getFileContents();
        $this->assertSame($content, $test, $message);
    }

    public function testStreamIsNeverWritable()
    {
        $this->assertFalse($this->stream->isWritable());
    }

    public function testCanReadStreamIteratively()
    {
        $body = '';
        while (! $this->stream->eof()) {
            $body .= $this->stream->read(128);
        }
        $this->assertStreamContents($body);
    }

    public function testGetContentsReturnsRemainingContentsOfStream()
    {
        $this->stream->read(128);
        $remainder = $this->stream->getContents();

        $contents = $this->getFileContents();
        $this->assertSame(substr($contents, 128), $remainder);
    }

    public function testGetContentsReturnCacheWhenReachedEof()
    {
        $this->stream->getContents();
        $this->assertStreamContents($this->stream->getContents());

        $stream = new PhpInputStream('data://,0');
        $stream->read(1);
        $stream->read(1);
        $this->assertSame('0', $stream->getContents(), 'Don\'t evaluate 0 as empty');
    }

    public function testCastingToStringReturnsFullContentsRegardlesOfPriorReads()
    {
        $this->stream->read(128);
        $this->assertStreamContents($this->stream->__toString());
    }

    public function testMultipleCastsToStringReturnSameContentsEvenIfReadsOccur()
    {
        $first  = (string) $this->stream;
        $this->stream->read(128);
        $second = (string) $this->stream;
        $this->assertSame($first, $second);
    }
}
