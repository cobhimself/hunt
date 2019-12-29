<?php

namespace Hunt\Tests\Bundle\Models;

use Hunt\Bundle\Models\Result;
use Hunt\Tests\HuntTestCase;
use SplFileObject;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @internal
 * @coversDefaultClass \Hunt\Bundle\Models\Result
 * @codeCoverageIgnore
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
     * @uses \Hunt\Bundle\Models\Result::setMatchingLines()
     */
    public function testGetLongestLineNumLength()
    {
        $this->result->setMatchingLines([
            0 => 'line 1',
            1 => 'line 2',
            20 => 'line 20', //2 digits
            400 => 'line 400', //3 digits is the expectation
        ]);

        $this->assertEquals(3, $this->result->getLongestLineNumLength());
    }

    /**
     * @covers ::getFileIterator
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
        $this->result->setMatchingLines([
            'line 1',
            'line 2',
            'line 3',
        ]);

        $this->assertEquals(3, $this->result->getNumMatches());
    }

    /**
     * @covers ::getMatchingLines
     * @covers ::setMatchingLines
     */
    public function testGetMatchingLines()
    {
        $lines = [
            'line 1',
            'line 2',
            'line 3',
            'line 4',
        ];

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
}
