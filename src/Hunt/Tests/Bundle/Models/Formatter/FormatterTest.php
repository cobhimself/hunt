<?php

namespace Hunt\Tests\Bundle\Models\Formatter;

use Hunt\Bundle\Exceptions\FormatterException;
use Hunt\Bundle\Exceptions\InvalidElementException;
use Hunt\Bundle\Models\Element\ElementInterface;
use Hunt\Bundle\Models\Element\Formatter\Formatter;
use Hunt\Bundle\Models\Element\Line\ContextLine;
use Hunt\Bundle\Models\Element\Line\ContextLineNumber;
use Hunt\Bundle\Models\Element\Line\Line;
use Hunt\Bundle\Models\Element\Line\LineFactory;
use Hunt\Bundle\Models\Element\Line\LineNumber;
use Hunt\Bundle\Models\Element\Line\Parts\Excluded;
use Hunt\Bundle\Models\Element\Line\Parts\Match;
use Hunt\Bundle\Models\Element\Line\Parts\Normal;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Hunt\Bundle\Models\Element\Formatter\Formatter
 */
class FormatterTest extends FormatterTestCase
{
    public function setUp()
    {
        $this->formatter = new Formatter();
    }

    /**
     * @covers ::setSidesForElement
     * @covers ::format
     * @covers ::getAfterContentForElement
     * @covers ::getBeforeContentForElement
     * @covers ::getElementClass
     * @covers ::setAfterContentForElement
     * @covers ::setBeforeContentForElement
     * @covers ::addElementSideContent
     * @covers ::getElementSideContent
     * @covers ::getFormattedLine
     *
     * @uses \Hunt\Bundle\Models\Element
     * @uses \Hunt\Bundle\Models\Element\Line\LineFactory
     * @uses \Hunt\Bundle\Models\Element\Line\AbstractLine
     * @uses \Hunt\Bundle\Models\Element\Line\Line
     * @uses \Hunt\Bundle\Models\Element\Line\ContextLine
     * @uses \Hunt\Bundle\Models\Element\Line\ParsedLine
     * @uses \Hunt\Bundle\Models\Element\Line\Parts\PartsCollection
     */
    public function testGetFormattedLine()
    {
        $this->formatter
            ->setSidesForElement(Line::class, '<line>', '</line>')
            ->setSidesForElement(LineNumber::class, '<lineNum>', '</lineNum>')
            ->setSidesForElement(ContextLine::class, '<context>', '</context>')
            ->setSidesForElement(ContextLineNumber::class, '<contextLineNum>', '</contextLineNum>')
            ->setSidesForElement(Normal::class, '<normal>', '</normal>')
            ->setSidesForElement(Match::class, '<match>', '</match>')
            ->setSidesForElement(Excluded::class, '<excluded>', '</excluded>');

        $normalLine = $this->getLine(1, 'This is a line');

        $this->assertEquals(
            '<lineNum>1</lineNum><line>This is a line</line>',
            $this->formatter->getFormattedLine($normalLine)
        );

        $contextLine = LineFactory::getContextLineFromLine($normalLine);
        $this->assertEquals(
            '<contextLineNum>1</contextLineNum><context>This is a line</context>',
            $this->formatter->getFormattedLine($contextLine)
        );


        $line = $this->getLine(1, 'This is a line with a match, an excluded part, and another match.');
        $parts = $this->getLinePartsForTest([
            'n1' => 'This is a line with a ',
            'm1' => 'match',
            'n2' => ', an ',
            'e1' => 'excluded',
            'n3' => ' part, and another ',
            'm2' => 'match',
            'n4' => '.'
        ]);

        $parsedLine = LineFactory::getParsed($line, $parts);

        $this->assertEquals(
            implode('',
                [
                    '<lineNum>1</lineNum>',
                    '<normal>This is a line with a </normal>',
                    '<match>match</match>',
                    '<normal>, an </normal>',
                    '<excluded>excluded</excluded>',
                    '<normal> part, and another </normal>',
                    '<match>match</match>',
                    '<normal>.</normal>',
                ]
            ),
            $this->formatter->getFormattedLine($parsedLine)
        );
    }

    /**
     * @covers ::setBeforeContentForElement
     * @covers ::getBeforeContentForElement
     * @covers ::setAfterContentForElement
     * @covers ::getAfterContentForElement
     * @covers ::addElementSideContent
     * @covers ::getElementClass
     * @covers ::getElementSideContent
     * @covers \Hunt\Bundle\Exceptions\InvalidElementException
     *
     * @dataProvider dataProviderForTestSetAndGetContentForElement
     *
     * @param string|ElementInterface $element           An element to add side content for.
     * @param array                   $expectedException Array with 'exception' and 'message' key/values representing
     *                                                   the expected exception we'd like to confirm. If empty, no
     *                                                   exception expectations are made.
     */
    public function testSetAndGetContentForElement(
        $element,
        array $expectedException = []
    ) {
        $afterContent = 'after';
        $beforeContent = 'before';

        if (!empty($expectedException)) {
            $this->expectException($expectedException['exception']);
            $this->expectExceptionMessage($expectedException['message']);
        }

        $this->formatter->setAfterContentForElement($element, $afterContent);
        $this->assertEquals($afterContent, $this->formatter->getAfterContentForElement($element));

        $this->formatter->setBeforeContentForElement($element, $beforeContent);
        $this->assertEquals($beforeContent, $this->formatter->getBeforeContentForElement($element));
    }

    public function dataProviderForTestSetAndGetContentForElement()
    {
        return [
            'line class' => [
                'element'   => Line::class,
            ],
            'line element' => [
                'element'   => $this->getLine(1, 'blah'),
            ],
            'bad element fqcn' => [
                'element' => Formatter::class,
                'exception' => [
                    'exception' => InvalidElementException::class,
                    'message'   => 'Invalid element type: Hunt\Bundle\Models\Element\Formatter\Formatter'
                ]
            ],
            'bad element class' => [
                'element' => new Formatter(),
                'exception' => [
                    'exception' => InvalidElementException::class,
                    'message'   => 'Invalid element type: Hunt\Bundle\Models\Element\Formatter\Formatter'
                ]
            ]
        ];
    }

    /**
     * @covers ::setSidesForElement
     * @covers ::addElementSideContent
     * @covers ::getAfterContentForElement
     * @covers ::getBeforeContentForElement
     * @covers ::getElementClass
     * @covers ::getElementSideContent
     * @covers ::setAfterContentForElement
     * @covers ::setBeforeContentForElement
     * @covers \Hunt\Bundle\Exceptions\FormatterException
     * @uses \Hunt\Bundle\Exceptions\InvalidElementException
     *
     * @dataProvider dataProviderForTestSetSidesForElement
     *
     * @param string|ElementInterface $element           An element to add side content for.
     * @param array                   $expectedException Array with 'exception' and 'message' key/values representing
     *                                                   the expected exception we'd like to confirm. If empty, no
     *                                                   exception expectations are made.
     */
    public function testSetSidesForElement($element, array $expectedException = [])
    {
        $beforeContent = 'before';
        $afterContent = 'after';

        if (!empty($expectedException)) {
            $this->expectException($expectedException['exception']);
            $this->expectExceptionMessage($expectedException['message']);
        }

        $this->formatter->setSidesForElement($element, $beforeContent, $afterContent);

        $this->assertEquals($afterContent, $this->formatter->getAfterContentForElement($element));
        $this->assertEquals($beforeContent, $this->formatter->getBeforeContentForElement($element));
    }

    public function dataProviderForTestSetSidesForElement()
    {
        return [
            'line class' => [
                'element'   => Line::class,
            ],
            'line element' => [
                'element'   => $this->getLine(1, 'blah'),
            ],
            'bad element fqcn' => [
                'element' => Formatter::class,
                'exception' => [
                    'exception' => FormatterException::class,
                    'message'   => 'Invalid element type: Hunt\Bundle\Models\Element\Formatter\Formatter'
                ]
            ],
            'bad element class' => [
                'element' => new Formatter(),
                'exception' => [
                    'exception' => FormatterException::class,
                    'message'   => 'Invalid element type: Hunt\Bundle\Models\Element\Formatter\Formatter'
                ]
            ]
        ];
    }
}
