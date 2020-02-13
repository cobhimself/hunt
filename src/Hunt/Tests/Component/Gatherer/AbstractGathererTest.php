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
     * @expectedException \RuntimeException
     * @covers ::highlightLine
     */
    public function testHighlightLine()
    {
        $this->gatherer->highlightLine('test line', self::HIGHLIGHT_START, self::HIGHLIGHT_END);
    }
}
