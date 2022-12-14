<?php

namespace Hunt\Tests\Bundle\Models\Formatter;

use Hunt\Bundle\Models\Element\Formatter\ConsoleFormatter;

class ConsoleFormatterTest extends FormatterTestCase
{
    public function setUp()
    {
        $this->formatter = new ConsoleFormatter();
    }

    public function testWrapElement()
    {
        $line = $this->getLine(1, 'This is my line');
        $this->formatter->wrapElement($line, 'blah');

        $this->assertEquals('<lineNumber>1</lineNumber><blah>This is my line</blah>', $this->formatter->getFormattedLine($line));
    }
}