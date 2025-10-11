<?php

declare(strict_types=1);

namespace Laminas\Diactoros;

use Override;
use Psr\Http\Message\StreamInterface;
use Stringable;

use const SEEK_SET;

/**
 * Wrapper for default Stream class, representing subpart (starting from given offset) of initial stream.
 * It can be used to avoid copying full stream, conserving memory.
 *
 * @see AbstractSerializer::splitStream()
 */
final class RelativeStream implements StreamInterface, Stringable
{
    private readonly int $offset;

    public function __construct(private readonly StreamInterface $decoratedStream, ?int $offset)
    {
        $this->offset = (int) $offset;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function __toString(): string
    {
        if ($this->isSeekable()) {
            $this->seek(0);
        }
        return $this->getContents();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function close(): void
    {
        $this->decoratedStream->close();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function detach()
    {
        return $this->decoratedStream->detach();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getSize(): ?int
    {
        $size = $this->decoratedStream->getSize();
        if ($size === null) {
            return null;
        }
        return $size - $this->offset;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function tell(): int
    {
        return $this->decoratedStream->tell() - $this->offset;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function eof(): bool
    {
        return $this->decoratedStream->eof();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function isSeekable(): bool
    {
        return $this->decoratedStream->isSeekable();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        if ($whence === SEEK_SET) {
            $this->decoratedStream->seek($offset + $this->offset, $whence);
            return;
        }
        $this->decoratedStream->seek($offset, $whence);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function isWritable(): bool
    {
        return $this->decoratedStream->isWritable();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function write(string $string): int
    {
        if ($this->tell() < 0) {
            throw new Exception\InvalidStreamPointerPositionException();
        }
        return $this->decoratedStream->write($string);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function isReadable(): bool
    {
        return $this->decoratedStream->isReadable();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function read(int $length): string
    {
        if ($this->tell() < 0) {
            throw new Exception\InvalidStreamPointerPositionException();
        }
        return $this->decoratedStream->read($length);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getContents(): string
    {
        if ($this->tell() < 0) {
            throw new Exception\InvalidStreamPointerPositionException();
        }
        return $this->decoratedStream->getContents();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getMetadata(?string $key = null)
    {
        return $this->decoratedStream->getMetadata($key);
    }
}
