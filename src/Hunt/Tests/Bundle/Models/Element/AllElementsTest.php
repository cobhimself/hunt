<?php

namespace Hunt\Tests\Bundle\Models;

use Hunt\Bundle\Models\Element;
use Hunt\Bundle\Models\Element\Line\ContextLineNumber;
use Hunt\Bundle\Models\Element\ContextSplit;
use Hunt\Bundle\Models\Element\Line\ContextLine;
use Hunt\Bundle\Models\Element\Line\Line;
use Hunt\Bundle\Models\Element\Line\ParsedLine;
use Hunt\Bundle\Models\Element\Line\Parts\Excluded;
use Hunt\Bundle\Models\Element\Line\Parts\Match;
use Hunt\Bundle\Models\Element\Line\Parts\Normal;
use Hunt\Bundle\Models\Element\Line\LineNumber;
use Hunt\Bundle\Models\Element\ResultFilePath;
use PHPUnit\Framework\TestCase;

class AllElementsTest extends TestCase
{

    public function testAllElements()
    {
        $elements = [
            LineNumber::class,
            ResultFilePath::class,
            ContextSplit::class,
            ContextLineNumber::class,
            ParsedLine::class,
            Line::class,
            ContextLine::class,
            Normal::class,
            Match::class,
            Excluded::class,
        ];

        foreach ($elements as $elementClass) {
            /**
             * @var Element\ElementInterface $element
             */
            $element = new $elementClass('content');

            $this->assertInstanceOf(Element::class, $element);
            $this->assertEquals('content', $element->getContent());
            $element->setContent('new content');
            $this->assertEquals('new content', $element->getContent());
        }
    }
}
