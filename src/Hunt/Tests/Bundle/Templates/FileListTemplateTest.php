<?php

namespace Hunt\Tests\Bundle\Templates;

use Hunt\Bundle\Templates\FileListTemplate;
use Hunt\Component\Gatherer\StringGatherer;

/**
 * @internal
 * @codeCoverageIgnore
 * @coversDefaultClass \Hunt\Bundle\Templates\FileListTemplate
 * @covers ::init
 *
 * @uses \Hunt\Bundle\Models\ResultCollection
 * @uses \Hunt\Bundle\Models\Result
 * @uses \Hunt\Bundle\Templates\AbstractTemplate
 * @uses \Hunt\Component\OutputStyler
 */
class FileListTemplateTest extends TemplateTestCase
{
    /**
     * @covers ::init
     * @covers ::setGatherer
     */
    public function setUp()
    {
        $this->template = new FileListTemplate();
        $this->template
            ->init($this->getResultCollection(), $this->getOutputMock())
            ->setGatherer(
                new StringGatherer(self::SEARCH_TERM, [self::EXCLUDE_TERM])
            );
    }

    /**
     * @covers ::getResultOutput
     *
     * @uses \Hunt\Component\Gatherer\StringGatherer::getHighlightedLine()
     */
    public function testGetResultOutput()
    {
        $expectedOutput = implode(\PHP_EOL, [
            'this/is/a/file/name/one',
            'this/is/a/file/name/two',
            'this/is/a/file/name/three',
        ]) . \PHP_EOL;

        $actualOutput = '';

        foreach ($this->getResultCollection() as $result) {
            $actualOutput .= $this->template->getResultOutput($result);
        }

        $this->assertEquals($expectedOutput, $actualOutput);
    }
}
