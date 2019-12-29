<?php

namespace Hunt\Tests\Bundle\Templates;

use Hunt\Bundle\Templates\AbstractTemplate;

class AbstractTemplateTest extends TemplateTestCase
{
    public function setUp()
    {
        $this->template = $this->getMockForAbstractClass(
            AbstractTemplate::class,
            [
                $this->getResultCollection(),
                $this->getOutputMock()
            ]
        );

        $this->template->method('getResultOutput')
            ->willReturn('test result output');
    }

    public function testGetResultLine()
    {
        $line = $this->template->getResultLine(100, 'this is the line', 'the');
        $this->assertEquals('100: this is the line', $line);
    }

    public function testGetTermResults()
    {
        $result = $this->getResultForFileConstant(self::RESULT_FILE_ONE);

        $this->assertEquals(
            [
                '1: this is line one',
                '2: this is line two',
                '3: line three has the ' . self::SEARCH_TERM
            ],
            $this->template->getTermResults($result)
        );
    }

    public function testGetHeader()
    {
        $this->template->setHeader('blah');
        $this->assertEquals('blah', $this->template->getHeader());
    }

    public function testGetFilename()
    {
        $fileName = $this->template->getFilename(
            $this->getResultWithFileInfoMock(self::SEARCH_TERM, self::RESULT_FILE_TWO)
        );
        $this->assertEquals(self::RESULT_FILE_TWO, $fileName);
    }

    public function testGetFooter()
    {
        $this->template->setFooter('bleh');
        $this->assertEquals('bleh', $this->template->getFooter());
    }
}
