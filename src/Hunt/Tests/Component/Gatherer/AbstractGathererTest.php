<?php

namespace Hunt\Tests\Component\Gatherer;

use Hunt\Component\Gatherer\AbstractGatherer;
use RuntimeException;

class AbstractGathererTest extends GathererTestCase
{
    public function setUp()
    {
        $this->gatherer = $this->getMockForAbstractClass(
            AbstractGatherer::class,
            [self::SEARCH_TERM, [self::EXCLUDE_TERM]]
        );
    }

    public function testGetTrimMatchingLines()
    {
        $this->gatherer->setTrimMatchingLines();
        $this->assertTrue($this->gatherer->getTrimMatchingLines());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testGather()
    {
        $this->gatherer->gather(
            $this->getResultWithFileInfoMock(self::SEARCH_TERM, self::RESULT_FILE_ONE)
        );
    }

    /**
     * @expectedException RuntimeException
     */
    public function testGetHighlightedLine()
    {
        $this->gatherer->getHighlightedLine('test line', self::HIGHLIGHT_START, self::HIGHLIGHT_END);
    }
}
