<?php

namespace Hunt\Tests\Bundle\Models;

use Hunt\Bundle\Models\MatchContext\MatchContext;
use Hunt\Bundle\Models\MatchContext\MatchContextCollection;
use Hunt\Bundle\Models\MatchContext\MatchContextCollectionFactory;
use Hunt\Tests\HuntTestCase;

/**
 * @coversDefaultClass \Hunt\Bundle\Models\MatchContext\MatchContextCollection
 * @codeCoverageIgnore
 * @covers ::addContext
 * @covers \Hunt\Bundle\Models\MatchContext\MatchContextCollectionFactory::get
 * @uses \Hunt\Bundle\Models\MatchContext\MatchContext
 */
class MatchContextCollectionTest extends HuntTestCase
{
    const BEFORE_ONE = [1 => 'one', 2 => 'two', 3 => 'three'];
    const AFTER_ONE = [5 => 'five', 6 => 'six', 7 => 'seven'];
    const BEFORE_TWO = [8 => 'eight', 9 => 'nine', 10 => 'ten'];
    const AFTER_TWO = [12 => 'twelve', 13 => 'thirteen', 14 => 'fourteen'];

    /**
     * @var MatchContextCollection
     */
    private $contextCollection;

    public function setUp()
    {
        $contextOne = new MatchContext(self::BEFORE_ONE, self::AFTER_ONE);
        $contextTwo = new MatchContext(self::BEFORE_TWO, self::AFTER_TWO);

        $this->contextCollection = MatchContextCollectionFactory::get(3);
        $this->contextCollection->addContext(4, $contextOne);
        $this->contextCollection->addContext(11, $contextTwo);
    }

    /**
     * @covers ::getContextForLine
     * @uses \Hunt\Bundle\Models\MatchContext\MatchContext
     */
    public function testMatchContextCollection()
    {
        $contextOne = $this->contextCollection->getContextForLine(4);
        $contextTwo = $this->contextCollection->getContextForLine(11);

        $this->assertEquals(self::BEFORE_ONE, $contextOne->getBefore());
        $this->assertEquals(self::AFTER_ONE, $contextOne->getAfter());
        $this->assertEquals(self::BEFORE_TWO, $contextTwo->getBefore());
        $this->assertEquals(self::AFTER_TWO, $contextTwo->getAfter());
    }

    /**
     * @expectedException \Hunt\Bundle\Exceptions\MissingMatchContextException
     * @covers ::getContextForLine
     * @covers \Hunt\Bundle\Exceptions\MissingMatchContextException
     */
    public function testMatchContextCollectionMissingContextException()
    {
        $collection = new MatchContextCollection();
        //No context exists. This should error out.
        $collection->getContextForLine(1);
    }

    /**
     * @covers ::getLongestLineNumberLength
     * @covers ::getLongestLineLength
     * @covers ::processLengths
     */
    public function testGetLineLengths()
    {
        $longestLineLength = $this->contextCollection->getLongestLineLength();
        $longestLineNumberLength = $this->contextCollection->getLongestLineNumberLength();

        $this->assertEquals(8, $longestLineLength);
        $this->assertEquals(2, $longestLineNumberLength);
    }

    /**
     * @covers ::getLongestLineLength
     * @covers ::getLongestLineNumberLength
     * @covers ::processLengths
     */
    public function testGetLineLengthsEmptyCollection()
    {
        $collection = new MatchContextCollection();
        $this->assertEquals(0, $collection->getLongestLineLength());
        $this->assertEquals(0, $collection->getLongestLineNumberLength());
    }

    /**
     * @covers \Hunt\Bundle\Models\MatchContext\DummyMatchContextCollection
     */
    public function testMatchContextCollectionFactory()
    {
        $collection = MatchContextCollectionFactory::get(0);
        $this->assertEquals($collection, MatchContextCollectionFactory::get(0));
    }

}
