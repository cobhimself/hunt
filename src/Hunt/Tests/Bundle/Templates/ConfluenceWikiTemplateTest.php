<?php

namespace Hunt\Tests\Bundle\Templates;

use Hunt\Bundle\Templates\ConfluenceWikiTemplate;
use Hunt\Component\Gatherer\StringGatherer;
use const PHP_EOL;

/**
 * @internal
 * @codeCoverageIgnore
 * @coversDefaultClass \Hunt\Bundle\Templates\ConfluenceWikiTemplate
 * @covers ::init
 *
 * @uses \Hunt\Component\Gatherer\AbstractGatherer::removeExcludedTerms()
 * @uses \Hunt\Component\Gatherer\AbstractGatherer::addExcludedTermsBack()
 * @uses \Hunt\Component\Gatherer\StringGatherer::highlightLine()
 * @uses \Hunt\Bundle\Models\ResultCollection
 * @uses \Hunt\Bundle\Models\Result
 * @uses \Hunt\Bundle\Templates\AbstractTemplate
 * @uses \Hunt\Component\OutputStyler
 * @uses \Hunt\Bundle\Models\MatchContext\MatchContextCollectionFactory
 * @uses \Hunt\Bundle\Models\MatchContext\MatchContext
 */
class ConfluenceWikiTemplateTest extends TemplateTestCase
{
    /**
     * @covers ::init
     * @covers ::setGatherer
     */
    public function setUp()
    {
        $this->template = new ConfluenceWikiTemplate();
        $this->template
            ->init($this->getResultCollection(), $this->getOutputMock())
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
     * @uses \Hunt\Bundle\Models\MatchContext\DummyMatchContextCollection
     * @uses \Hunt\Bundle\Models\MatchContext\MatchContext
     * @uses \Hunt\Bundle\Models\MatchContext\MatchContextCollection
     *
     * @dataProvider dataProviderForTestGetResultOutput
     */
    public function testGetResultOutput(bool $showContext, array $expectations)
    {
        $this->template->setShowContext($showContext);
        $expectedOutput = implode(PHP_EOL, $expectations) . PHP_EOL;

        $actualOutput = '';

        foreach ($this->getResultCollection($showContext) as $result) {
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

    public function dataProviderForTestGetResultOutput()
    {
        return [
            'without context lines' => [
                'addContext' => false,
                'expectations' => [
                    '|{status:title= |color=red}|this/is/a/file/name/one|{noformat:nopanel=true}',
                    '1: this is line one',
                    '2: this is line two',
                    '3: line three has the *' . self::SEARCH_TERM . '*',
                    '{noformat}|',
                    '|{status:title= |color=red}|this/is/a/file/name/two|{noformat:nopanel=true}',
                    '1: this is line one',
                    '2: this is line two with the *' . self::SEARCH_TERM . '*',
                    '3: this is line three with *' . self::SEARCH_TERM . '*Ok',
                    '{noformat}|',
                    '|{status:title= |color=red}|this/is/a/file/name/three|{noformat:nopanel=true}',
                    '1: this is line one and it has the *' . self::SEARCH_TERM . '* as well as ' . self::EXCLUDE_TERM,
                    '2: this is line two',
                    '300: this is line three hundred',
                    '{noformat}|',
                ]
            ],
            //This is a bit convoluted but, since we're pretending each of the lines in our result lis is a match,
            //our context lines are basically the same for each line.
            'with context lines' => [
                'addContext' => true,
                'expectations' => [
                    '|{status:title= |color=red}|this/is/a/file/name/one|{noformat:nopanel=true}',
                    '1: this is line one',
                    '2: this is line two',
                    '3: line three has the ' . self::SEARCH_TERM,
                    '{noformat}',
                    '{noformat:nopanel=true}',
                    '1: this is line one',
                    '2: this is line two',
                    '3: line three has the ' . self::SEARCH_TERM,
                    '{noformat}',
                    '{noformat:nopanel=true}',
                    '1: this is line one',
                    '2: this is line two',
                    '3: line three has the *' . self::SEARCH_TERM . '*',
                    '{noformat}|',
                    '|{status:title= |color=red}|this/is/a/file/name/two|{noformat:nopanel=true}',
                    '1: this is line one',
                    '2: this is line two with the ' . self::SEARCH_TERM,
                    '3: this is line three with ' . self::SEARCH_TERM . 'Ok',
                    '{noformat}',
                    '{noformat:nopanel=true}',
                    '1: this is line one',
                    '2: this is line two with the *' . self::SEARCH_TERM . '*',
                    '3: this is line three with ' . self::SEARCH_TERM . 'Ok',
                    '{noformat}',
                    '{noformat:nopanel=true}',
                    '1: this is line one',
                    '2: this is line two with the ' . self::SEARCH_TERM,
                    '3: this is line three with *' . self::SEARCH_TERM . '*Ok',
                    '{noformat}|',
                    '|{status:title= |color=red}|this/is/a/file/name/three|{noformat:nopanel=true}',
                    '1: this is line one and it has the *' . self::SEARCH_TERM . '* as well as ' . self::EXCLUDE_TERM,
                    '2: this is line two',
                    '300: this is line three hundred',
                    '{noformat}',
                    '{noformat:nopanel=true}',
                    '1: this is line one and it has the ' . self::SEARCH_TERM . ' as well as ' . self::EXCLUDE_TERM,
                    '2: this is line two',
                    '300: this is line three hundred',
                    '{noformat}',
                    '{noformat:nopanel=true}',
                    '1: this is line one and it has the ' . self::SEARCH_TERM . ' as well as ' . self::EXCLUDE_TERM,
                    '2: this is line two',
                    '300: this is line three hundred',
                    '{noformat}|',
                ]
            ],
        ];
    }
}
