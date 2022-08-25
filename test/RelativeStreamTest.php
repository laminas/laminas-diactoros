<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros;

use Laminas\Diactoros\RelativeStream;
use Laminas\Diactoros\Stream;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use const SEEK_SET;

/**
 * @covers \Laminas\Diactoros\RelativeStream
 */
class RelativeStreamTest extends TestCase
{
    public function testToString(): void
    {
        $decorated = $this->createMock(Stream::class);
        $decorated->method('isSeekable')->willReturn(true);
        $decorated->method('tell')->willReturn(100);
        $decorated->expects(self::once())->method('seek')->with(100, SEEK_SET);
        $decorated->expects(self::once())->method('getContents')->willReturn('foobarbaz');

        $stream = new RelativeStream($decorated, 100);
        $ret    = $stream->__toString();
        $this->assertSame('foobarbaz', $ret);
    }

    public function testClose(): void
    {
        $decorated = $this->createMock(Stream::class);
        $decorated->expects(self::once())->method('close');
        $stream = new RelativeStream($decorated, 100);
        $stream->close();
    }

    public function testDetach(): void
    {
        $decorated = $this->createMock(Stream::class);
        $decorated->expects(self::once())->method('detach')->willReturn(250);
        $stream = new RelativeStream($decorated, 100);
        $ret    = $stream->detach();
        $this->assertSame(250, $ret);
    }

    public function testGetSize(): void
    {
        $decorated = $this->createMock(Stream::class);
        $decorated->expects(self::once())->method('getSize')->willReturn(250);
        $stream = new RelativeStream($decorated, 100);
        $ret    = $stream->getSize();
        $this->assertSame(150, $ret);
    }

    public function testTell(): void
    {
        $decorated = $this->createMock(Stream::class);
        $decorated->expects(self::once())->method('tell')->willReturn(188);
        $stream = new RelativeStream($decorated, 100);
        $ret    = $stream->tell();
        $this->assertSame(88, $ret);
    }

    public function testIsSeekable(): void
    {
        $decorated = $this->createMock(Stream::class);
        $decorated->expects(self::once())->method('isSeekable')->willReturn(true);
        $stream = new RelativeStream($decorated, 100);
        $ret    = $stream->isSeekable();
        $this->assertSame(true, $ret);
    }

    public function testIsWritable(): void
    {
        $decorated = $this->createMock(Stream::class);
        $decorated->expects(self::once())->method('isWritable')->willReturn(true);
        $stream = new RelativeStream($decorated, 100);
        $ret    = $stream->isWritable();
        $this->assertSame(true, $ret);
    }

    public function testIsReadable(): void
    {
        $decorated = $this->createMock(Stream::class);
        $decorated->expects(self::once())->method('isReadable')->willReturn(false);
        $stream = new RelativeStream($decorated, 100);
        $ret    = $stream->isReadable();
        $this->assertSame(false, $ret);
    }

    public function testSeek(): void
    {
        $decorated = $this->createMock(Stream::class);
        $decorated->expects(self::once())->method('seek')->with(126, SEEK_SET);
        $stream = new RelativeStream($decorated, 100);
        $this->assertNull($stream->seek(26));
    }

    public function testRewind(): void
    {
        $decorated = $this->createMock(Stream::class);
        $decorated->expects(self::once())->method('seek')->with(100, SEEK_SET);
        $stream = new RelativeStream($decorated, 100);
        $this->assertNull($stream->rewind());
    }

    public function testWrite(): void
    {
        $decorated = $this->createMock(Stream::class);
        $decorated->method('tell')->willReturn(100);
        $decorated->expects(self::once())->method('write')->with('foobaz')->willReturn(6);
        $stream = new RelativeStream($decorated, 100);
        $ret    = $stream->write("foobaz");
        $this->assertSame(6, $ret);
    }

    public function testRead(): void
    {
        $decorated = $this->createMock(Stream::class);
        $decorated->method('tell')->willReturn(100);
        $decorated->expects(self::once())->method('read')->with(3)->willReturn('foo');
        $stream = new RelativeStream($decorated, 100);
        $ret    = $stream->read(3);
        $this->assertSame("foo", $ret);
    }

    public function testGetContents(): void
    {
        $decorated = $this->createMock(Stream::class);
        $decorated->method('tell')->willReturn(100);
        $decorated->expects(self::once())->method('getContents')->willReturn('foo');
        $stream = new RelativeStream($decorated, 100);
        $ret    = $stream->getContents();
        $this->assertSame("foo", $ret);
    }

    public function testGetMetadata(): void
    {
        $decorated = $this->createMock(Stream::class);
        $decorated->expects(self::once())->method('getMetadata')->with('bar')->willReturn('foo');
        $stream = new RelativeStream($decorated, 100);
        $ret    = $stream->getMetadata("bar");
        $this->assertSame("foo", $ret);
    }

    public function testWriteRaisesExceptionWhenPointerIsBehindOffset(): void
    {
        $decorated = $this->createMock(Stream::class);
        $decorated->expects(self::once())->method('tell')->willReturn(0);
        $decorated->expects(self::never())->method('write')->with('foobaz');
        $stream = new RelativeStream($decorated, 100);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid pointer position');

        $stream->write("foobaz");
    }

    public function testReadRaisesExceptionWhenPointerIsBehindOffset(): void
    {
        $decorated = $this->createMock(Stream::class);
        $decorated->expects(self::once())->method('tell')->willReturn(0);
        $decorated->expects(self::never())->method('read')->with(3);
        $stream = new RelativeStream($decorated, 100);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid pointer position');

        $stream->read(3);
    }

    public function testGetContentsRaisesExceptionWhenPointerIsBehindOffset(): void
    {
        $decorated = $this->createMock(Stream::class);
        $decorated->expects(self::once())->method('tell')->willReturn(0);
        $decorated->expects(self::never())->method('getContents');
        $stream = new RelativeStream($decorated, 100);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid pointer position');

        $stream->getContents();
    }

    public function testCanReadContentFromNotSeekableResource(): void
    {
        $decorated = $this->createMock(Stream::class);
        $decorated->method('isSeekable')->willReturn(false);
        $decorated->expects(self::never())->method('seek');
        $decorated->method('tell')->willReturn(3);
        $decorated->method('getContents')->willReturn('CONTENTS');

        $stream = new RelativeStream($decorated, 3);
        $this->assertSame('CONTENTS', $stream->__toString());
    }
}
