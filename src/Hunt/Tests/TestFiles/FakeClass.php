<?php

class FakeClass
{
    /**
     * This will be a blah property.
     *
     * @var string
     */
    private $blah;

    /**
     * FakeClass constructor.
     *
     * We'll mark it as deprecated. Maybe we'll end up searching for files with this tag in it.
     *
     * @deprecated
     */
    public function __construct()
    {
        $this->blah = 'blah';
    }

    public function doWerk()
    {
        $this->blah = 'werk';
    }
}
