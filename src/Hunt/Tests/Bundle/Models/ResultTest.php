<?php

namespace Hunt\Tests\Bundle\Models;

use Hunt\Bundle\Models\MatchContext\MatchContextCollection;
use Hunt\Bundle\Models\Result;
use Hunt\Tests\HuntTestCase;
use SplFileObject;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @internal
 * @coversDefaultClass \Hunt\Bundle\Models\Result
 * @codeCoverageIgnore
 *
 * @uses \Hunt\Bundle\Models\MatchContext\DummyMatchContextCollection
 */
class ResultTest extends HuntTestCase
{
    /**
     * @var Result
     */
    private $result;

    public function setUp()
    {
        $this->result = $this->getResultWithFileInfoMock(
            self::SEARCH_TERM,
            self::RESULT_FILE_ONE
        );
    }

    /**
     * @covers ::getFileName
     */
    public function testGetFileName()
    {
        $this->assertEquals(self::RESULT_FILE_ONE, $this->result->getFileName());
    }

    /**
     * @covers ::getTerm
     */
    public function testGetTerm()
    {
        $this->assertEquals(self::SEARCH_TERM, $this->result->getTerm());
    }

    /**
     * @covers ::setMatchingLines
     */
    public function testSetMatchingLines()
    {
        /* @noinspection UnnecessaryAssertionInspection */
        $this->assertInstanceOf(Result::class, $this->result->setMatchingLines([]));
    }

    /**
     * @covers ::getLongestLineNumLength
     * @covers ::getContextCollection
     * @covers ::setMatchingLines
     */
    public function testGetLongestLineNumLength()
    {
        $this->result
            ->addMatchingLine($this->getLine(1, 'line 1'))
            ->addMatchingLine($this->getLine(2, 'line 2'))
            //2 digits
            ->addMatchingLine($this->getLine(20, 'line 20'))
            //3 digits is the expectation
            ->addMatchingLine($this->getLine(400, 'line 400'));

        $this->assertEquals(3, $this->result->getLongestLineNumLength());
    }

    /**
     * @covers ::setContextCollection
     * @covers ::getContextCollection
     */
    public function testSetGetContextCollection()
    {
        $this->result->setContextCollection(new MatchContextCollection());

        $this->assertInstanceOf(MatchContextCollection::class, $this->result->getContextCollection());
    }

    /**
     * @covers ::getLongestLineNumLength
     */
    public function testGetLongestLineNumLengthEmptyResult()
    {
        $this->assertEquals(0, $this->result->getLongestLineNumLength());
    }

    /**
     * @covers ::getFileIterator
     *
     * @uses \Hunt\Bundle\Models\Result::getFile()
     */
    public function testGetFileIterator()
    {
        /* @noinspection UnnecessaryAssertionInspection */
        $this->assertInstanceOf(SplFileObject::class, $this->result->getFileIterator());
    }

    /**
     * @covers ::getNumMatches
     * @covers ::setMatchingLines
     */
    public function testGetNumMatches()
    {
        $this->result->setMatchingLines($this->buildLineArray(3));

        $this->assertEquals(3, $this->result->getNumMatches());
    }

    /**
     * @covers ::getMatchingLines
     * @covers ::setMatchingLines
     */
    public function testGetMatchingLines()
    {

        $lines = $this->buildLineArray(4);

        $this->result->setMatchingLines($lines);

        $this->assertEquals($lines, $this->result->getMatchingLines());
    }

    /**
     * @covers ::getFile
     */
    public function testGetFile()
    {
        /* @noinspection UnnecessaryAssertionInspection */
        $this->assertInstanceOf(SplFileInfo::class, $this->result->getFile());
    }

    /**
     * For code coverage
     * @covers ::trimResults
     * @covers ::getMatchingLines
     */
    public function testDoNotTrimWhenNoMatchesExist()
    {
        $this->result->trimResults();
        $this->assertEmpty($this->result->getMatchingLines());
    }
}
