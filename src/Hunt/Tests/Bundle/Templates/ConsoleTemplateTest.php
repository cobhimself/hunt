<?php

namespace Hunt\Tests\Bundle\Templates;

use Hunt\Bundle\Templates\ConsoleTemplate;
use Hunt\Component\Gatherer\StringGatherer;
use PHPUnit\Framework\TestCase;

class ConsoleTemplateTest extends TemplateTestCase
{
    public function setUp()
    {
        $this->template = new ConsoleTemplate($this->getResultCollection(), $this->getOutputMock());
        $this->template
            ->setGatherer(
                new StringGatherer(self::SEARCH_TERM, [self::EXCLUDE_TERM])
            )
            ->highlight();
    }

    public function testGetResultOutput()
    {
        $expectedOutput = implode(PHP_EOL, [
            //Our line numbers should take up three spaces since our longest line number has three digits.
            '  this/is/a/file/name/one:   1: this is line one',
            //Notice how we've padded the results with spaces.
            '                             2: this is line two',
            //We will see our search term highlighted.
            '                             3: line three has the *' . self::SEARCH_TERM . '*',
            '  this/is/a/file/name/two:   1: this is line one',
            //We will see our search term highlighted.
            '                             2: this is line two with the *' . self::SEARCH_TERM . '*',
            //This line will have a partial highlight because we do not exclude our seach term when it ends in Ok
            '                             3: this is line three with *' . self::SEARCH_TERM . '*Ok',
            //This line will have our search time highlighted but there will not be a partial match on our excluded term
            'this/is/a/file/name/three:   1: this is line one and it has the *' . self::SEARCH_TERM . '* as well as ' . self::EXCLUDE_TERM,
            '                             2: this is line two',
            '                           300: this is line three hundred'
        ]) . PHP_EOL;

        $actualOutput = '';

        foreach ($this->getResultCollection() as $result) {
            $actualOutput .= $this->template->getResultOutput($result);
        }

        $this->assertEquals($expectedOutput, $actualOutput);
    }

    /**
     * Confirm our line numbers are returned with padding.
     */
    public function testGetLineNumber()
    {
        $this->assertEquals('  1', $this->template->getLineNumber('1'));
        $this->assertEquals(' 21', $this->template->getLineNumber('21'));
        $this->assertEquals('321', $this->template->getLineNumber('321'));
    }
}
