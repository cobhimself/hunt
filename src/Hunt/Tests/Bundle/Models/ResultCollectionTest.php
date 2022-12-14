<?php

namespace Hunt\Tests\Bundle\Models;

use Hunt\Bundle\Models\ResultCollection;
use Hunt\Tests\HuntTestCase;
use InvalidArgumentException;
use const SORT_ASC;
use const SORT_DESC;

/**
 * @internal
 * @coversDefaultClass \Hunt\Bundle\Models\ResultCollection
 * @covers ::addResult
 * @covers \Hunt\Bundle\Models\Result::setContextCollection
 *
 * @uses \Hunt\Bundle\Models\Result::setMatchingLines()
 * @uses \Hunt\Bundle\Models\Result::getFileName()
 * @uses \Hunt\Bundle\Models\MatchContext\MatchContext
 * @uses \Hunt\Bundle\Models\MatchContext\DummyMatchContextCollection
 * @uses \Hunt\Bundle\Models\MatchContext\MatchContextCollectionFactory
 *
 * @codeCoverageIgnore
 */
class ResultCollectionTest extends HuntTestCase
{
    /**
     * @var ResultCollection
     */
    private $resultCollection;

    public function setUp()
    {
        $this->resultCollection = $this->getResultCollectionWithFileConstants();
    }

    /**
     * @covers ::getLongestFilenameLength
     */
    public function testGetLongestFilenameLength()
    {
        $this->assertEquals(
            strlen(self::RESULT_FILE_THREE),
            $this->resultCollection->getLongestFilenameLength()
        );
    }

    /**
     * @covers ::getLongestLineNumInResults
     *
     * @uses \Hunt\Bundle\Models\Result::getLongestLineNumLength()
     * @uses \Hunt\Bundle\Models\Result::getContextCollection()
     */
    public function testGetLongestLineNumInResults()
    {
        $this->assertEquals(3, $this->resultCollection->getLongestLineNumInResults());
    }

    /**
     * @param int   $sortDir      the sort direction to use
     * @param array $expectedKeys expected filename order
     *
     * @covers ::sortByFilename
     * @dataProvider dataProviderForTestSortByFilename
     */
    public function testSortByFilename(int $sortDir, array $expectedKeys)
    {
        if (0 !== $sortDir) {
            $this->resultCollection->sortByFilename($sortDir);
        }
        $this->assertEquals($expectedKeys, $this->resultCollection->keys());
    }

    /**
     * @expectedException InvalidArgumentException
     * @covers ::sortByFilename
     */
    public function testSortByFilenameInvalidSortArgument()
    {
        $this->resultCollection->sortByFilename(-1);
    }

    /**
     * @covers ::squashEmptyResults
     *
     * @uses \Hunt\Bundle\Models\Result::getNumMatches()
     */
    public function testSquashEmptyResults()
    {
        //Lets remove all matching lines from our second file so we can confirm the second file is squashed
        $this->resultMatchingLines[self::RESULT_FILE_TWO] = [];
        $this->resetFinalResultMatchingLines();
        $this->resultCollection = $this->getResultCollectionWithFileConstants();

        $this->resultCollection->squashEmptyResults();

        $this->assertEquals(
            [
                self::RESULT_FILE_ONE,
                self::RESULT_FILE_THREE,
            ],
            $this->resultCollection->keys()
        );
    }

    public function dataProviderForTestSortByFilename(): array
    {
        return [
            'test ascending' => [
                SORT_ASC,
                [
                    'this/is/a/file/name/one',
                    'this/is/a/file/name/three',
                    'this/is/a/file/name/two',
                ],
            ],
            'test descending' => [
                SORT_DESC,
                [
                    'this/is/a/file/name/two',
                    'this/is/a/file/name/three',
                    'this/is/a/file/name/one',
                ],
            ],
            'test no sort' => [
                0,
                [
                    'this/is/a/file/name/one',
                    'this/is/a/file/name/two',
                    'this/is/a/file/name/three',
                ],
            ],
        ];
    }
}
