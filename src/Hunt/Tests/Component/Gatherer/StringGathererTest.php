<?php

namespace Hunt\Tests\Component\Gatherer;

use Hunt\Component\Gatherer\StringGatherer;

/**
 * @internal
 */
class StringGathererTest extends GathererTestCase
{
    public function setUp()
    {
    }

    public function testGather()
    {
        $this->gatherer = new StringGatherer(self::SEARCH_TERM, [self::EXCLUDE_TERM]);
        $result = $this->getResultWithFileInfoMock(self::SEARCH_TERM, self::RESULT_FILE_ONE);
        $this->gatherer->gather($result);

        $this->assertEquals([], $result->getMatchingLines());
    }

    /**
     * NOTE: Our data provider provides strings which do not have our search string in order to better stress test the
     * code. This should never happen because our Results will only have lines matching the search term.
     *
     * @dataProvider dataProviderTestGetHighlightedLine
     */
    public function testGetHighlightedLine(string $searchTerm, array $excludeTerm, string $line, string $expectation)
    {
        $this->gatherer = new StringGatherer($searchTerm, $excludeTerm);
        $result = $this->gatherer->getHighlightedLine($line, self::HIGHLIGHT_START, self::HIGHLIGHT_END);
        $this->assertEquals(
            $expectation,
            $result,
            sprintf('Expected (%s) to become (%s)', $line, $expectation)
        );
    }
}
