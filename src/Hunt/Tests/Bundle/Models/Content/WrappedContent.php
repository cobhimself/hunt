<?php

namespace Hunt\Tests\Bundle\Models\Content;


class WrappedContent extends Content
{
    public function wrap(string $wrapper)
    {
        $this->content = '<' . $wrapper . '>' . $this->content . '</' . $wrapper . '>';
    }
}