<?php

namespace Hunt\Tests\Component\Gatherer;


use Hunt\Component\Gatherer\GathererInterface;
use Hunt\Tests\HuntTestCase;

class GathererTestCase extends HuntTestCase
{
    /**
     * @var GathererInterface
     */
    protected $gatherer;

    /**
     * Provides a long list of lines to test against.
     *
     * @return array
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
            '1234'       => '123456'
        ];

        /*
         * Create a set of unique term replacement placeholders so we can put them in our test templates and then
         * replace them with our term combinations.
         */
        $term            = '%^$';
        $highlightedTerm = '&*(';
        $excludedTerm    = '!@#';

        /**
         * Add as many lines/expectations as necessary. The three variables above will be replaced with the term
         * combinations when we compile the final data.
         */
        $testTemplates = [
            [
                'line'     => 'this is a line without our search term',
                'expected' => 'this is a line without our search term'
            ],
            [
                'line'     => 'this line will end with our ' . $term,
                'expected' => 'this line will end with our ' . $highlightedTerm
            ],
            [
                'line'     => $term . ' will start this sentence.',
                'expected' => $highlightedTerm . ' will start this sentence.'
            ],
            [
                'line'     => 'Our ' . $term . ' will not be starting this sentence.',
                'expected' => 'Our ' . $highlightedTerm . ' will not be starting this sentence.'
            ],
            [
                'line'     => 'Our ' . $excludedTerm . ' should not be highlighted.',
                'expected' => 'Our ' . $excludedTerm . ' should not be highlighted.'
            ],
            [
                'line'     => $excludedTerm . ' should not be highlighted.',
                'expected' => $excludedTerm . ' should not be highlighted.'
            ],
            [
                'line'     => 'The following should not be highlighted: ' . $excludedTerm,
                'expected' => 'The following should not be highlighted: ' . $excludedTerm
            ],
            [
                'line'     => $term . $excludedTerm,
                'expected' => $highlightedTerm . $excludedTerm
            ],
            [
                'line'     => $excludedTerm . $term,
                'expected' => $excludedTerm . $highlightedTerm
            ],
        ];

        $counter = 0;

        //Compile our final data
        foreach ($termCombinations as $searchTerm => $excludeTerm) {
            //This list of strings will be replaced
            $replace = [$term, $highlightedTerm, $excludedTerm];
            //with this list of strings
            $with    = [$searchTerm, self::HIGHLIGHT_START . $searchTerm . self::HIGHLIGHT_END, $excludeTerm];

            //Create a new set of test templates for the current term combinations.
            foreach ($testTemplates as $template) {
                $counter++;
                $replacedLine = str_replace($replace, $with, $template['line']);
                $replacedExpected = str_replace($replace, $with, $template['expected']);

                $compiledData[$counter . ': (' . $replacedLine . ') => (' . $replacedExpected . ')'] = [
                    'searchTerm'  => $searchTerm,
                    'excludeTerm' => [$excludeTerm],
                    'line'        => $replacedLine,
                    'expected'    => $replacedExpected
                ];
            }
        }

        return $compiledData;
    }
}
