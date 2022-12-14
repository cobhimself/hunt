<?php

namespace Hunt\Tests\Bundle\Templates;

use Hunt\Bundle\Models\Element\Formatter\ConsoleFormatter;
use Hunt\Bundle\Models\Element\Line\LineFactory;
use Hunt\Bundle\Templates\AbstractTemplate;
use Hunt\Component\Gatherer\StringGatherer;

/**
 * @internal
 * @codeCoverageIgnore
 * @coversDefaultClass \Hunt\Bundle\Templates\AbstractTemplate
 *
 * @uses \Hunt\Bundle\Models\Result
 * @uses \Hunt\Bundle\Models\ResultCollection
 * @uses \Hunt\Component\Gatherer\AbstractGatherer::removeExcludedTerms()
 * @uses \Hunt\Component\Gatherer\AbstractGatherer::addExcludedTermsBack()
 * @uses \Hunt\Component\Gatherer\StringGatherer::getHighlightedLine()
 * @uses \Hunt\Component\Gatherer\StringGatherer::highlightLine()
 * @uses \Hunt\Component\OutputStyler
 * @uses \Hunt\Bundle\Models\MatchContext\MatchContextCollectionFactory
 * @uses \Hunt\Bundle\Models\MatchContext\MatchContext
 * @covers ::setGatherer
 */
class AbstractTemplateTest extends TemplateTestCase
{
    public function setUp()
    {
        $this->template = $this->getMockForAbstractClass(AbstractTemplate::class);
        $this->template
            ->init($this->getResultCollection(), $this->getOutputMock());

        $this->template->method('getResultOutput')
            ->willReturn('test result output');
    }

    /**
     * @covers ::doHighlight
     * @covers ::getHighlightEnd
     * @covers ::getHighlightStart
     * @covers ::getLineNumber
     * @covers ::getResultLine
     * @covers ::highlight
     * @covers ::setHighlightEnd
     * @covers ::setHighlightStart
     * @dataProvider dataProviderForTestGetResultLine
     */
    public function testGetResultLine(
        bool $highlight,
        int $lineNum,
        string $line,
        string $expectation,
        string $highlightStart = null,
        string $highlightEnd = null
    ) {

        $line = LineFactory::getLine($lineNum, $line);

        $this->template->highlight($highlight);

        $resultLine = $this->template->getResultLine($line);

        $this->assertEquals($expectation, $resultLine);
    }

    public function dataProviderForTestGetResultLine(): array
    {
        $line = 'this is the ' . self::SEARCH_TERM;

        return [
            'no highlight' => [
                'highlight'   => false,
                'lineNum'     => 100,
                'line'        => $line,
                'expectation' => '100: this is the ' . self::SEARCH_TERM,
            ],
            'highlight with default' => [
                'highlight'   => true,
                'lineNum'     => 100,
                'line'        => $line,
                'expectation' => '100: this is the *' . self::SEARCH_TERM . '*',
            ],
            'highlight with %/#' => [
                'highlight'      => true,
                'lineNum'        => 100,
                'line'           => $line,
                'expectation'    => '100: this is the %' . self::SEARCH_TERM . '#',
                'highlightStart' => '%',
                'highlightEnd'   => '#',
            ],
        ];
    }

    /**
     * @covers ::doHighlight
     * @covers ::getLineNumber
     * @covers ::getResultLine
     * @covers ::getTermResults
     * @covers ::processContextLines
     * @covers ::getContextSplitAfter
     * @covers ::getContextSplitBefore
     * @covers ::setShowContext
     * @covers ::getShowContext
     *
     * @uses \Hunt\Bundle\Models\Result::getMatchingLines()
     * @uses \Hunt\Bundle\Models\Result::getTerm()
     * @uses \Hunt\Bundle\Models\Result::getFileName()
     * @uses \Hunt\Bundle\Models\MatchContext\DummyMatchContextCollection
     * @uses \Hunt\Bundle\Models\MatchContext\MatchContext
     * @uses \Hunt\Bundle\Models\MatchContext\MatchContextCollection
     *
     * @dataProvider dataProviderForTestGetTermResults
     */
    public function testGetTermResults(bool $addContext, array $expectation)
    {
        $result = $this->getResultForFileConstant(self::RESULT_FILE_ONE, $addContext);
        $this->template->setShowContext($addContext);

        $this->assertEquals(
            $expectation,
            $this->template->getTermResults($result)
        );
    }

    /**
     * @covers ::getHeader
     * @covers ::setHeader
     */
    public function testGetHeader()
    {
        $this->template->setHeader('blah');
        $this->assertEquals('blah', $this->template->getHeader());
    }

    /**
     * @covers ::getFilename
     *
     * @uses \Hunt\Bundle\Models\Result::getFileName()
     */
    public function testGetFilename()
    {
        $fileName = $this->template->getFilename(
            $this->getResultWithFileInfoMock(self::SEARCH_TERM, self::RESULT_FILE_TWO)
        );
        $this->assertEquals(self::RESULT_FILE_TWO, $fileName);
    }

    /**
     * @covers ::getFooter
     * @covers ::setFooter
     */
    public function testGetFooter()
    {
        $this->template->setFooter('bleh');
        $this->assertEquals('bleh', $this->template->getFooter());
    }

    public function dataProviderForTestGetTermResults(): array
    {
        return [
            'no context lines' => [
                'addContext' => false,
                'expectation' => [
                    '1: this is line one',
                    '2: this is line two',
                    '3: line three has the ' . self::SEARCH_TERM,
                ],
            ],
            'with context lines' => [
                'addContext' => true,
                'expectation' => [
                    '---',
                    '1: this is line one', //match
                    '2: this is line two', //context
                    '3: line three has the ' . self::SEARCH_TERM, //context
                    '---',
                    '---',
                    '1: this is line one', //context
                    '2: this is line two', //match
                    '3: line three has the ' . self::SEARCH_TERM, //context
                    '---',
                    '---',
                    '1: this is line one', //context
                    '2: this is line two', //context
                    '3: line three has the ' . self::SEARCH_TERM, //match
                    '---',
                ],
            ],
        ];
    }
}
