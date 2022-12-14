<?php

namespace Hunt\Tests\Bundle\Models\Element\Line;

use Hunt\Bundle\Models\Element\Line\ContextLine;
use Hunt\Bundle\Models\Element\Line\Line;
use Hunt\Bundle\Models\Element\Line\LineFactory;
use Hunt\Bundle\Models\Element\Line\ParsedLine;
use Hunt\Tests\HuntTestCase;

class LineFactoryTest extends HuntTestCase
{
    public function testGetParsed()
    {
        $line = new Line('content');
        $line->setLineNumber(1);

        $parts = $this->getLinePartsForTest([
            'n1' => 'content',
        ]);

        $parsed = LineFactory::getParsed($line, $parts);

        $this->assertInstanceOf(ParsedLine::class, $parsed);

    }

    public function testGetLine()
    {
        $line = LineFactory::getLine(1, 'content');

        $this->assertInstanceOf(Line::class, $line);
        $this->assertEquals('content', $line->getContent());
        $this->assertEquals(1, $line->getLineNumber());
    }

    public function testGetContextLineFromLine()
    {
        $line = LineFactory::getLine(1, 'content');
        $contextLine = LineFactory::getContextLineFromLine($line);

        $this->assertInstanceOf(ContextLine::class, $contextLine);
        $this->assertEquals('content', $contextLine->getContent());
        $this->assertEquals(1, $contextLine->getLineNumber());
    }
}
