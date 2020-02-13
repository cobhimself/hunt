<?php

namespace Hunt\Tests\Component\Gatherer;

use Hunt\Component\Gatherer\StringGatherer;

/**
 * @coversDefaultClass \Hunt\Component\Gatherer\StringGatherer
 * @codeCoverageIgnore
 *
 * @covers \Hunt\Component\Gatherer\AbstractGatherer::addExcludedTermsBack()
 * @covers \Hunt\Component\Gatherer\AbstractGatherer::removeExcludedTerms()
 * @covers \Hunt\Component\Gatherer\AbstractGatherer::gather
 *
 * @uses \Hunt\Bundle\Models\Result
 * @uses \Hunt\Component\MatchContext\ContextCollector
 * @uses \Hunt\Bundle\Models\MatchContext\DummyMatchContextCollection
 * @uses \Hunt\Component\MatchContext\DummyContextCollector
 *
 * @internal
 */
class StringGathererTest extends GathererTestCase
{
    /**
     * @covers ::gather
     * @covers ::lineMatches
     * @covers ::getNumContextLines
     * @covers \Hunt\Component\MatchContext\ContextCollectorFactory::get
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
        $this->gatherer = new StringGatherer($searchTerm, $excludeTerm);
        $result = $this->gatherer->getHighlightedLine($line, self::HIGHLIGHT_START, self::HIGHLIGHT_END);
        $this->assertEquals(
            $expectation,
            $result,
            sprintf('Expected (%s) to become (%s)', $line, $expectation)
        );
    }

    /**
     * Provides a long list of lines to test against.
     */
    public function dataProviderTestGetHighlightedLine(): array
    {
        //We will compile our final data here.
        $compiledData = [];

        /*
         * Create a set of search terms and exclude terms which will allow us to confirm partial matches
         * are not highlighted.
         */
        $termCombinations = [
            'searchTerm' => 'searchTermExcluded',
            'PHPUnit_'   => 'PHPUnit_Framework_MockObjects_MockObject',
            '1234'       => '123456',
        ];

        /*
         * Create a set of unique term replacement placeholders so we can put them in our test templates and then
         * replace them with our term combinations.
         */
        $term = '%^$';
        $highlightedTerm = '&*(';
        $excludedTerm = '!@#';

        /**
         * Add as many lines/expectations as necessary. The three variables above will be replaced with the term
         * combinations when we compile the final data.
         */
        $testTemplates = [
            [
                'line'     => 'this is a line without our search term',
                'expected' => 'this is a line without our search term',
            ],
            [
                'line'     => 'this line will end with our ' . $term,
                'expected' => 'this line will end with our ' . $highlightedTerm,
            ],
            [
                'line'     => $term . ' will start this sentence.',
                'expected' => $highlightedTerm . ' will start this sentence.',
            ],
            [
                'line'     => 'Our ' . $term . ' will not be starting this sentence.',
                'expected' => 'Our ' . $highlightedTerm . ' will not be starting this sentence.',
            ],
            [
                'line'     => 'Our ' . $excludedTerm . ' should not be highlighted.',
                'expected' => 'Our ' . $excludedTerm . ' should not be highlighted.',
            ],
            [
                'line'     => $excludedTerm . ' should not be highlighted.',
                'expected' => $excludedTerm . ' should not be highlighted.',
            ],
            [
                'line'     => 'The following should not be highlighted: ' . $excludedTerm,
                'expected' => 'The following should not be highlighted: ' . $excludedTerm,
            ],
            [
                'line'     => $term . $excludedTerm,
                'expected' => $highlightedTerm . $excludedTerm,
            ],
            [
                'line'     => $excludedTerm . $term,
                'expected' => $excludedTerm . $highlightedTerm,
            ],
        ];

        $counter = 0;

        //Compile our final data
        foreach ($termCombinations as $searchTerm => $excludeTerm) {
            //This list of strings will be replaced
            $replace = [$term, $highlightedTerm, $excludedTerm];
            //with this list of strings
            $with = [$searchTerm, self::HIGHLIGHT_START . $searchTerm . self::HIGHLIGHT_END, $excludeTerm];

            //Create a new set of test templates for the current term combinations.
            foreach ($testTemplates as $template) {
                ++$counter;
                $replacedLine = str_replace($replace, $with, $template['line']);
                $replacedExpected = str_replace($replace, $with, $template['expected']);

                $compiledData[$counter . ': (' . $replacedLine . ') => (' . $replacedExpected . ')'] = [
                    'searchTerm'  => $searchTerm,
                    'excludeTerm' => [$excludeTerm],
                    'line'        => $replacedLine,
                    'expected'    => $replacedExpected,
                ];
            }
        }

        return $compiledData;
    }
}
