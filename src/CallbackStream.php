<?php

declare(strict_types=1);

namespace Laminas\Diactoros;

use Override;
use Psr\Http\Message\StreamInterface;
use Stringable;

use function array_key_exists;

use const SEEK_SET;

/**
 * Implementation of PSR HTTP streams
 */
class CallbackStream implements StreamInterface, Stringable
{
    /** @var callable|null */
    protected $callback;

    /**
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(callable $callback)
    {
        $this->attach($callback);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function __toString(): string
    {
        return $this->getContents();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function close(): void
    {
        $this->callback = null;
    }

    /**
     * {@inheritdoc}
     *
     * @return null|callable
     */
    #[Override]
    public function detach(): ?callable
    {
        $callback       = $this->callback;
        $this->callback = null;
        return $callback;
    }

    /**
     * Attach a new callback to the instance.
     */
    public function attach(callable $callback): void
    {
        $this->callback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getSize(): ?int
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function tell(): int
    {
        throw Exception\UntellableStreamException::forCallbackStream();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function eof(): bool
    {
        return $this->callback === null;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function isSeekable(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        throw Exception\UnseekableStreamException::forCallbackStream();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function rewind(): void
    {
        throw Exception\UnrewindableStreamException::forCallbackStream();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function isWritable(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function write(string $string): int
    {
        throw Exception\UnwritableStreamException::forCallbackStream();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function isReadable(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function read(int $length): string
    {
        throw Exception\UnreadableStreamException::forCallbackStream();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getContents(): string
    {
        $callback = $this->detach();
        return $callback !== null ? (string) $callback() : '';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getMetadata(?string $key = null)
    {
        $metadata = [
            'eof'         => $this->eof(),
            'stream_type' => 'callback',
            'seekable'    => false,
        ];

        if (null === $key) {
            return $metadata;
        }

        if (! array_key_exists($key, $metadata)) {
            return null;
        }

        return $metadata[$key];
    }
}
