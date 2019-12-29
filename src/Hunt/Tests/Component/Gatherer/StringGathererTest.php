<?php

namespace Hunt\Tests\Component\Gatherer;

use Hunt\Component\Gatherer\StringGatherer;

/**
 * @coversDefaultClass \Hunt\Component\Gatherer\StringGatherer
 * @codeCoverageIgnore
 * @uses \Hunt\Bundle\Models\Result
 * @internal
 */
class StringGathererTest extends GathererTestCase
{
    /**
     * @covers ::gather
     */
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
     * @covers ::getHighlightedLine
     * @dataProvider dataProviderTestGetHighlightedLine
     *
     * @param string $searchTerm the search term to highlight
     * @param array $excludeTerm an array of terms to exclude in highlighting
     * @param string $line the line to perform highlighting on
     * @param string $expectation the final line we expect after highlighting
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
