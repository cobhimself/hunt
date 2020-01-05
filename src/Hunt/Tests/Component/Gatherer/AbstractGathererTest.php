<?php

namespace Hunt\Tests\Component\Gatherer;

use Hunt\Component\Gatherer\AbstractGatherer;

/**
 * @internal
 * @coversDefaultClass \Hunt\Component\Gatherer\AbstractGatherer
 * @codeCoverageIgnore
 */
class AbstractGathererTest extends GathererTestCase
{
    public function setUp()
    {
        $this->gatherer = $this->getMockForAbstractClass(
            AbstractGatherer::class,
            [self::SEARCH_TERM, [self::EXCLUDE_TERM]]
        );
    }

    /**
     * @covers ::getTrimMatchingLines
     * @covers ::setTrimMatchingLines
     */
    public function testGetTrimMatchingLines()
    {
        $this->gatherer->setTrimMatchingLines();
        $this->assertTrue($this->gatherer->getTrimMatchingLines());
    }

    /**
     * @expectedException \RuntimeException
     * @covers ::gather
     */
    public function testGather()
    {
        $this->gatherer->gather(
            $this->getResultWithFileInfoMock(self::SEARCH_TERM, self::RESULT_FILE_ONE)
        );
    }

    /**
     * @expectedException \RuntimeException
     * @covers ::getHighlightedLine
     */
    public function testGetHighlightedLine()
    {
        $this->gatherer->getHighlightedLine('test line', self::HIGHLIGHT_START, self::HIGHLIGHT_END);
    }
}
