<?php

namespace Hunt\Tests\Bundle\Models;

use Hunt\Bundle\Models\Result;
use Hunt\Tests\HuntTestCase;
use SplFileObject;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @internal
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
            self::EXPECTED_FILE_NAME
        );
    }

    public function testGetFileName()
    {
        $this->assertEquals(self::EXPECTED_FILE_NAME, $this->result->getFileName());
    }

    public function testGetTerm()
    {
        $this->assertEquals(self::SEARCH_TERM, $this->result->getTerm());
    }

    public function testSetMatchingLines()
    {
        /* @noinspection UnnecessaryAssertionInspection */
        $this->assertInstanceOf(Result::class, $this->result->setMatchingLines([]));
    }

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

    public function testGetFileIterator()
    {
        /* @noinspection UnnecessaryAssertionInspection */
        $this->assertInstanceOf(SplFileObject::class, $this->result->getFileIterator());
    }

    public function testGetNumMatches()
    {
        $this->result->setMatchingLines([
            'line 1',
            'line 2',
            'line 3',
        ]);

        $this->assertEquals(3, $this->result->getNumMatches());
    }

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

    public function testGetFile()
    {
        /* @noinspection UnnecessaryAssertionInspection */
        $this->assertInstanceOf(SplFileInfo::class, $this->result->getFile());
    }
}
