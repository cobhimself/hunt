<?php

namespace Hunt\Tests\Bundle\Models;

use Hunt\Bundle\Models\Result;
use Hunt\Bundle\Models\ResultCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;

class ResultCollectionTest extends TestCase
{
    const FILENAME_ONE = 'this/is/a/file/name';
    const FILENAME_TWO = 'this/is/another/file/name';
    const FILENAME_THREE = 'this/is/a/really/long/file/name';

    /**
     * @var ResultCollection
     */
    private $resultCollection;

    public function setUp()
    {

        $this->resultCollection = $this->getResultCollection(
            'searchTerm',
            [
                self::FILENAME_ONE => [
                    'longest_line' => 5
                ],
                self::FILENAME_TWO => [
                    'longest_line' => 7
                ],
                self::FILENAME_THREE => [
                    'longest_line' => 1
                ]
            ]
        );
    }

    public function testGetLongestFilenameLength()
    {
        $this->assertEquals(strlen(self::FILENAME_THREE), $this->resultCollection->getLongestFilenameLength());
    }

    public function testGetLongestLineNumInResults()
    {
        $this->assertEquals(7, $this->resultCollection->getLongestLineNumInResults());
    }

    /**
     * @param string $term
     * @param array $fileOptions
     *
     * @return ResultCollection
     */
    private function getResultCollection(string $term, array $fileOptions): ResultCollection
    {
        $results = [];

        foreach ($fileOptions as $fileName => $expectations) {
            /** @var MockObject $mock */
            $mock = $this->getMockBuilder(Result::class)
                ->setConstructorArgs([
                    $term,
                    $fileName,
                    $this->createMock(SplFileInfo::class)
                ])
                ->setMethods(['getLongestLineNumLength'])
                ->getMock();

            $mock->method('getLongestLineNumLength')
                ->willReturn($expectations['longest_line']);

            $results[$fileName] = $mock;
        }

        return new ResultCollection($results);
    }
}
