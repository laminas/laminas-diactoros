<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros;

use Laminas\Diactoros\CallbackStream;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \Laminas\Diactoros\CallbackStream
 */
class CallbackStreamTest extends TestCase
{
    public function testToString() : void
    {
        $stream = new CallbackStream(function () {
            return 'foobarbaz';
        });

        $ret = $stream->__toString();
        $this->assertSame('foobarbaz', $ret);
    }

    public function testClose() : void
    {
        $stream = new CallbackStream(function () {
        });

        $stream->close();

        $callback = $stream->detach();

        $this->assertNull($callback);
    }

    public function testDetach() : void
    {
        $callback = function () {
        };
        $stream = new CallbackStream($callback);
        $ret = $stream->detach();
        $this->assertSame($callback, $ret);
    }

    public function testEof() : void
    {
        $stream = new CallbackStream(function () {
        });
        $ret = $stream->eof();
        $this->assertFalse($ret);

        $stream->getContents();
        $ret = $stream->eof();
        $this->assertTrue($ret);
    }

    public function testGetSize() : void
    {
        $stream = new CallbackStream(function () {
        });
        $ret = $stream->getSize();
        $this->assertNull($ret);
    }

    public function testTell() : void
    {
        $stream = new CallbackStream(function () {
        });

        $this->expectException(RuntimeException::class);

        $stream->tell();
    }

    public function testIsSeekable() : void
    {
        $stream = new CallbackStream(function () {
        });
        $ret = $stream->isSeekable();
        $this->assertFalse($ret);
    }

    public function testIsWritable() : void
    {
        $stream = new CallbackStream(function () {
        });
        $ret = $stream->isWritable();
        $this->assertFalse($ret);
    }

    public function testIsReadable() : void
    {
        $stream = new CallbackStream(function () {
        });
        $ret = $stream->isReadable();
        $this->assertFalse($ret);
    }

    public function testSeek() : void
    {
        $stream = new CallbackStream(function () {
        });

        $this->expectException(RuntimeException::class);

        $stream->seek(0);
    }

    public function testRewind() : void
    {
        $stream = new CallbackStream(function () {
        });

        $this->expectException(RuntimeException::class);

        $stream->rewind();
    }

    public function testWrite() : void
    {
        $stream = new CallbackStream(function () {
        });

        $this->expectException(RuntimeException::class);

        $stream->write('foobarbaz');
    }

    public function testRead() : void
    {
        $stream = new CallbackStream(function () {
        });

        $this->expectException(RuntimeException::class);

        $stream->read(3);
    }

    public function testGetContents() : void
    {
        $stream = new CallbackStream(function () {
            return 'foobarbaz';
        });

        $ret = $stream->getContents();
        $this->assertSame('foobarbaz', $ret);
    }

    public function testGetMetadata() : void
    {
        $stream = new CallbackStream(function () {
        });

        $ret = $stream->getMetadata('stream_type');
        $this->assertSame('callback', $ret);

        $ret = $stream->getMetadata('seekable');
        $this->assertFalse($ret);

        $ret = $stream->getMetadata('eof');
        $this->assertFalse($ret);

        $all = $stream->getMetadata();
        $this->assertSame([
            'eof' => false,
            'stream_type' => 'callback',
            'seekable' => false,
        ], $all);

        $notExists = $stream->getMetadata('boo');
        $this->assertNull($notExists);
    }

    public function phpCallbacksForStreams() : array
    {
        $class = TestAsset\CallbacksForCallbackStreamTest::class;

        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'instance-method' => [[new TestAsset\CallbacksForCallbackStreamTest(), 'sampleCallback'], $class . '::sampleCallback'],
            'static-method'   => [[$class, 'sampleStaticCallback'], $class . '::sampleStaticCallback'],
        ];
        // phpcs:enable Generic.Files.LineLength.TooLong
    }

    /**
     * @dataProvider phpCallbacksForStreams
     */
    public function testAllowsArbitraryPhpCallbacks($callback, $expected) : void
    {
        $stream = new CallbackStream($callback);
        $contents = $stream->getContents();
        $this->assertSame($expected, $contents);
    }
}
