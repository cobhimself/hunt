<?php

namespace Hunt\Tests\Bundle\Templates;

use Hunt\Bundle\Templates\AbstractTemplate;
use Hunt\Component\Gatherer\StringGatherer;

/**
 * @internal
 * @codeCoverageIgnore
 * @coversDefaultClass \Hunt\Bundle\Templates\AbstractTemplate
 *
 * @uses \Hunt\Bundle\Models\Result
 * @uses \Hunt\Bundle\Models\ResultCollection
 * @uses \Hunt\Component\Gatherer\StringGatherer::getHighlightedLine()
 * @uses \Hunt\Component\OutputStyler
 * @covers ::setGatherer
 */
class AbstractTemplateTest extends TemplateTestCase
{
    public function setUp()
    {
        $this->template = $this->getMockForAbstractClass(AbstractTemplate::class);
        $this->template
            ->init($this->getResultCollection(), $this->getOutputMock())
            ->setGatherer(new StringGatherer(self::SEARCH_TERM));

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
        if (null !== $highlightStart) {
            $this->template->setHighlightStart($highlightStart);
        }

        if (null !== $highlightEnd) {
            $this->template->setHighlightEnd($highlightEnd);
        }

        $this->template->highlight($highlight);

        $resultLine = $this->template->getResultLine($lineNum, $line, self::SEARCH_TERM);

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
     *
     * @uses \Hunt\Bundle\Models\Result::getMatchingLines()
     * @uses \Hunt\Bundle\Models\Result::getTerm()
     * @uses \Hunt\Bundle\Models\Result::getFileName()
     */
    public function testGetTermResults()
    {
        $result = $this->getResultForFileConstant(self::RESULT_FILE_ONE);

        $this->assertEquals(
            [
                '1: this is line one',
                '2: this is line two',
                '3: line three has the ' . self::SEARCH_TERM,
            ],
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
}
