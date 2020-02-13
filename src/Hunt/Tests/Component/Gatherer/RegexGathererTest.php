<?php

namespace Hunt\Tests\Component\Gatherer;

use Hunt\Component\Gatherer\RegexGatherer;

/**
 * @coversDefaultClass \Hunt\Component\Gatherer\RegexGatherer
 * @codeCoverageIgnore
 *
 * @uses \Hunt\Bundle\Models\Result
 * @uses \Hunt\Component\MatchContext\ContextCollector
 * @uses \Hunt\Component\MatchContext\ContextCollectorFactory
 * @uses \Hunt\Component\MatchContext\DummyContextCollector
 * @uses \Hunt\Bundle\Models\MatchContext\DummyMatchContextCollection
 * @covers \Hunt\Component\Gatherer\AbstractGatherer::addExcludedTermsBack()
 * @covers \Hunt\Component\Gatherer\AbstractGatherer::removeExcludedTerms()
 *
 * @internal
 */
class RegexGathererTest extends GathererTestCase
{
    /*
     * Create a set of search terms and exclude terms which will allow us to confirm partial matches
     * are not highlighted.
     */
    protected $termCombinations = [
    ];

    /**
     * @covers ::gather
     * @covers ::lineMatches
     * @covers ::getNumContextLines
     * @covers \Hunt\Component\MatchContext\ContextCollectorFactory
     * @covers \Hunt\Bundle\Models\MatchContext\DummyMatchContextCollection
     * @covers \Hunt\Component\MatchContext\DummyContextCollector
     *
     * @uses \Hunt\Bundle\Models\Result
     */
    public function testGather()
    {
        $this->gatherer = new RegexGatherer(self::SEARCH_TERM, [self::EXCLUDE_TERM]);
        $result = $this->getResultWithFileInfoMock(self::SEARCH_TERM, self::RESULT_FILE_ONE);
        $this->gatherer->gather($result);

        $this->assertEquals([], $result->getMatchingLines());
    }

    /**
     * NOTE: Our data provider provides strings which do not have our search string in order to better stress test the
     * code. This should never happen because our Results will only have lines matching the search term.
     *
     * @covers ::getHighlightedLine
     * @covers ::highlightLine
     * @dataProvider dataProviderTestGetHighlightedLine
     *
     * @param string $searchTerm  the search term to highlight
     * @param array  $excludeTerm an array of terms to exclude in highlighting
     * @param string $line        the line to perform highlighting on
     * @param string $expectation the final line we expect after highlighting
     */
    public function testGetHighlightedLine(string $searchTerm, array $excludeTerm, string $line, string $expectation)
    {
        $this->gatherer = new RegexGatherer($searchTerm, $excludeTerm);
        $result = $this->gatherer->getHighlightedLine($line, self::HIGHLIGHT_START, self::HIGHLIGHT_END);
        $this->assertEquals(
            $expectation,
            $result,
            sprintf('Expected (%s) to become (%s)', $line, $expectation)
        );
    }

    public function dataProviderTestGetHighlightedLine(): array
    {
        return [
            'simple regex' => [
                'term'     => '/search/',
                'excluded' => [],
                'line'     => 'this will include our search term',
                'expected' => 'this will include our *search# term',
            ],
            'simple regex with capturing group' => [
                'term'     => '/(search)/',
                'excluded' => [],
                'line'     => 'this will include our search term',
                'expected' => 'this will include our *search# term',
            ],
            'simple regex with capturing group and two results' => [
                'term'     => '/(search)/',
                'excluded' => [],
                'line'     => 'this search will include our search term',
                'expected' => 'this *search# will include our *search# term',
            ],
            'digit test' => [
                'term'     => '/\d{3}/',
                'excluded' => ['111'],
                'line'     => 'This is 1, but this is 11, and this is 111. What about 1234?',
                'expected' => 'This is 1, but this is 11, and this is 111. What about *123#4?',
            ],
            'digit test with group' => [
                'term'     => '/(\d{3})/',
                'excluded' => ['111'],
                'line'     => 'This is 1, but this is 11, and this is 111. What about 1234?',
                'expected' => 'This is 1, but this is 11, and this is 111. What about *123#4?',
            ],
            'matching middle' => [
                'term'     => '/PHPUnit_(.*)_MockObject/',
                'excluded' => [],
                'line'     => 'PHPUnit_Framework_MockObjects_MockObject phpunitframeworkmockobjects',
                'expected' => 'PHPUnit_*Framework_MockObjects#_MockObject phpunitframeworkmockobjects',
            ],
            'matching middle, nongreedy' => [
                'term'     => '/PHPUnit_(.*?)_MockObject/',
                'excluded' => [],
                'line'     => 'PHPUnit_Framework_MockObjects_MockObject PHPUnit_Framework_MockObjects_MockObject',
                'expected' => 'PHPUnit_*Framework#_MockObjects_MockObject PHPUnit_*Framework#_MockObjects_MockObject',
            ],
        ];
    }
}
