<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Diactoros;

use function stream_get_contents;

/**
 * Caching version of php://input
 */
class PhpInputStream extends Stream
{
    /**
     * @var string
     */
    private $cache = '';

    /**
     * @var bool
     */
    private $reachedEof = false;

    /**
     * @param  string|resource $stream
     */
    public function __construct($stream = 'php://input')
    {
        parent::__construct($stream, 'r');
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        if ($this->reachedEof) {
            return $this->cache;
        }

        $this->getContents();
        return $this->cache;
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length)
    {
        $content = parent::read($length);
        if (! $this->reachedEof) {
            $this->cache .= $content;
        }

        if ($this->eof()) {
            $this->reachedEof = true;
        }

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents($maxLength = -1)
    {
        if ($this->reachedEof) {
            return $this->cache;
        }

        $contents     = stream_get_contents($this->resource, $maxLength);
        $this->cache .= $contents;

        if ($maxLength === -1 || $this->eof()) {
            $this->reachedEof = true;
        }

        return $contents;
    }
}
