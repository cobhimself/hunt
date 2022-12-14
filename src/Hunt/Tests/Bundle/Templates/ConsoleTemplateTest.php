<?php

namespace Hunt\Tests\Bundle\Templates;

use Hunt\Bundle\Templates\ConsoleTemplate;
use Hunt\Component\Gatherer\StringGatherer;
use const PHP_EOL;

/**
 * @internal
 * @codeCoverageIgnore
 * @coversDefaultClass \Hunt\Bundle\Templates\ConsoleTemplate
 * @covers ::init
 *
 * @uses \Hunt\Bundle\Models\ResultCollection
 * @uses \Hunt\Bundle\Models\Result
 * @uses \Hunt\Component\Gatherer\AbstractGatherer::removeExcludedTerms()
 * @uses \Hunt\Component\Gatherer\AbstractGatherer::addExcludedTermsBack()
 * @uses \Hunt\Component\Gatherer\StringGatherer::highlightLine()
 * @uses \Hunt\Bundle\Templates\AbstractTemplate
 * @uses \Hunt\Component\OutputStyler
 * @uses \Hunt\Bundle\Models\MatchContext\DummyMatchContextCollection
 * @uses \Hunt\Bundle\Models\MatchContext\MatchContext
 * @uses \Hunt\Bundle\Models\MatchContext\MatchContextCollectionFactory
 */
class ConsoleTemplateTest extends TemplateTestCase
{
    /**
     * @covers ::setGatherer
     */
    public function setUp()
    {
        $this->template = new ConsoleTemplate();
        $this->template->init($this->getResultCollection(), $this->getOutputMock())
            ->setGatherer(
                new StringGatherer(self::SEARCH_TERM, [self::EXCLUDE_TERM])
            )
            ->highlight();
    }

    /**
     * @covers ::doHighlight
     * @covers ::getHighlightEnd
     * @covers ::getHighlightStart
     * @covers ::getLineNumber
     * @covers ::getResultLine
     * @covers ::getResultOutput
     * @covers ::getContextSplitBefore
     * @covers ::getContextSplitAfter
     * @covers ::setShowContext
     * @covers ::getShowContext
     *
     * @uses \Hunt\Component\Gatherer\StringGatherer::getHighlightedLine()
     * @uses \Hunt\Bundle\Models\MatchContext\MatchContextCollection
     *
     * @dataProvider dataProviderForTestGetResultOutput
     */
    public function testGetResultOutput(bool $addContext, array $expectations)
    {
        $this->template->setShowContext($addContext);

        $expectedOutput = implode(PHP_EOL, $expectations) . PHP_EOL;

        $actualOutput = '';

        foreach ($this->getResultCollection($addContext) as $result) {
            $actualOutput .= $this->template->getResultOutput($result);
        }

        $this->assertEquals($expectedOutput, $actualOutput);
    }

    /**
     * Confirm our line numbers are returned with padding.
     *
     * @covers ::getLineNumber
     */
    public function testGetLineNumber()
    {
        $this->assertEquals('  1', $this->template->getLineNumber('1'));
        $this->assertEquals(' 21', $this->template->getLineNumber('21'));
        $this->assertEquals('321', $this->template->getLineNumber('321'));
    }

    public function dataProviderForTestGetResultOutput(): array
    {
        return [
            'no context lines' => [
                'addContext' => false,
                'expectations' => [
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
                    '                           300: this is line three hundred',
                ]
            ],
            //This is a bit convoluted but, since we're pretending each of the lines in our result lis is a match,
            //our context lines are basically the same for each line.
            'with context lines' => [
                'addContext' => true,
                'expectations' => [
                    '  this/is/a/file/name/one: ---',
                    //We're pretending this is our first match. No before context, only after. Search term will not be
                    //highlighted because it is in context lines.
                    '                             1: this is line one',
                    '                             2: this is line two',
                    '                             3: line three has the ' . self::SEARCH_TERM,
                    '                           ---',
                    '                           ---',
                    '                             1: this is line one',
                    //We're pretending this is our second match. Will have before and after context but our search term
                    //within the context will not be highlighted.
                    '                             2: this is line two',
                    '                             3: line three has the ' . self::SEARCH_TERM,
                    '                           ---',
                    '                           ---',
                    '                             1: this is line one',
                    '                             2: this is line two',
                    //This is our matching line, it will have before context but none after. The search term will be
                    //highlighted here since it is in the matching line.
                    '                             3: line three has the *' . self::SEARCH_TERM . '*',
                    '                           ---',
                    '  this/is/a/file/name/two: ---',
                    //We're pretending this is our first match. No before context, only after. Search term within
                    //context lines will not be highlighted.
                    '                             1: this is line one',
                    '                             2: this is line two with the ' . self::SEARCH_TERM,
                    '                             3: this is line three with ' . self::SEARCH_TERM . 'Ok',
                    '                           ---',
                    '                           ---',
                    '                             1: this is line one',
                    //We're pretending this is our second match. Will have before and after context but our search term
                    //within the context will not be highlighted.
                    '                             2: this is line two with the *' . self::SEARCH_TERM . '*',
                    '                             3: this is line three with ' . self::SEARCH_TERM . 'Ok',
                    '                           ---',
                    '                           ---',
                    '                             1: this is line one',
                    '                             2: this is line two with the ' . self::SEARCH_TERM,
                    //This is our matching line, it will have before context but none after. The search term will be
                    //highlighted here since it is in the matching line but not in the context lines.
                    '                             3: this is line three with *' . self::SEARCH_TERM . '*Ok',
                    '                           ---',
                    'this/is/a/file/name/three: ---',
                    //We're pretending this is our first match. No before context, only after.
                    '                             1: this is line one and it has the *' . self::SEARCH_TERM . '* as well as ' . self::EXCLUDE_TERM,
                    '                             2: this is line two',
                    '                           300: this is line three hundred',
                    '                           ---',
                    '                           ---',
                    '                             1: this is line one and it has the ' . self::SEARCH_TERM . ' as well as ' . self::EXCLUDE_TERM,
                    //We're pretending this is our second match. Will have before and after context but our search term
                    //within the context will not be highlighted.
                    '                             2: this is line two',
                    '                           300: this is line three hundred',
                    '                           ---',
                    '                           ---',
                    '                             1: this is line one and it has the ' . self::SEARCH_TERM . ' as well as ' . self::EXCLUDE_TERM,
                    '                             2: this is line two',
                    //We're pretending this is our final match. The search term will not be highlighted in the context
                    //lines which come before.
                    '                           300: this is line three hundred',
                    '                           ---',
                ]
            ]
        ];
    }
}
