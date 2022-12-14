<?php


namespace Hunt\Bundle\Models\Element;


interface ElementInterface
{
    public function __construct(string $content);

    public function getContent(): string;
    public function setContent(string $content): ElementInterface;
}