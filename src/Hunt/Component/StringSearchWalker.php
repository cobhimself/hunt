<?php

namespace Hunt\Component;


use Iterator;

class StringSearchWalker implements Iterator
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var string
     */
    private $needle;

    /**
     * @var string
     */
    private $head;

    /**
     * @var int
     */
    private $needleLength;

    /**
     * @var int
     */
    private $cursorPos = 0;

    /**
     * @var string
     */
    private $tail;

    /**
     * @var string
     */
    private $original;

    public function __construct(string $content, string $needle)
    {
        $this->original = $content;
        $this->content = $content;
        $this->needle = $needle;
        $this->needleLength = strlen($needle);
    }

    /**
     * Return the current element
     * @link https://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        $this->head = strstr($this->tail, $this->needle, true);

        return $this->head;
    }

    /**
     * Move forward to next element
     * @link https://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        $this->cursorPos += strlen($this->head) + $this->needleLength;
    }

    /**
     * Return the key of the current element
     * @link https://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return $this->head;
    }

    /**
     * Checks if current position is valid
     * @link https://php.net/manual/en/iterator.valid.php
     * @return bool The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid(): bool
    {
        $this->tail = substr($this->content, $this->cursorPos);

        return strpos($this->tail, $this->needle) !== false;
    }

    /**
     * Rewind the Iterator to the first element
     * @link https://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->content = $this->original;
        $this->head = null;
        $this->tail = null;
        $this->cursorPos = 0;
    }

    public function tail(): string
    {
        return $this->tail;
    }
}