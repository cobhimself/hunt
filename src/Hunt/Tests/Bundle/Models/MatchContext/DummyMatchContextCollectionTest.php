<?php

namespace Hunt\Tests\Bundle\Models\MatchContext;

use Hunt\Bundle\Models\MatchContext\DummyMatchContextCollection;
use Hunt\Bundle\Models\MatchContext\MatchContext;
use Hunt\Tests\HuntTestCase;

/**
 * @coversDefaultClass \Hunt\Bundle\Models\MatchContext\DummyMatchContextCollection
 * @codeCoverageIgnore
 *
 * @covers \Hunt\Bundle\Models\MatchContext\DummyMatchContextCollection::__construct
 * @uses \Hunt\Bundle\Models\MatchContext\MatchContext
 */
class DummyMatchContextCollectionTest extends HuntTestCase
{
    /**
     * @var DummyMatchContextCollection
     */
    private $matchContextCollection;

    public function setUp()
    {
        $this->matchContextCollection = new DummyMatchContextCollection();
    }

    /**
     * @covers ::getContextForLine
     */
    public function test__construct()
    {
        //Confirm any new attempt to obtain our MatchContext refers to the exact same MatchContext regardless of which
        //line we want the context for.
        $this->assertEquals(
            $this->matchContextCollection->getContextForLine(2),
            (new DummyMatchContextCollection())->getContextForLine(1)
        );
    }

    /**
     * @covers ::getLongestLineLength
     */
    public function testGetLongestLineLength()
    {
        $this->assertEquals(0, $this->matchContextCollection->getLongestLineLength());
    }

    /**
     * @covers ::getLongestLineNumberLength
     */
    public function testGetLongestLineNumberLength()
    {
        $this->assertEquals(0, $this->matchContextCollection->getLongestLineNumberLength());
    }

    /**
     * @covers ::addContext
     * @covers ::getContextForLine
     */
    public function testAddContext()
    {
        //Add a match context
        $this->matchContextCollection->addContext(100, new MatchContext([], []));

        //Confirm we still use the exact same MatchContext object regardless of what line we call it for.
        $this->assertEquals(
            $this->matchContextCollection->getContextForLine(3),
            $this->matchContextCollection->getContextForLine(100)
        );
    }

    /**
     * @covers ::processLengths
     * @covers ::getLongestLineNumberLength
     * @covers ::getLongestLineLength
     */
    public function testProcessLengths()
    {
        //Process the lengths (noop)
        $this->matchContextCollection->processLengths();

        //confirm our lengths are still 0
        $this->assertEquals(0, $this->matchContextCollection->getLongestLineLength());
        $this->assertEquals(0, $this->matchContextCollection->getLongestLineNumberLength());
    }

    /**
     * @covers ::getCollectionSize
     */
    public function testGetCollectionSize()
    {
        $this->assertEquals(0, $this->matchContextCollection->getCollectionSize());
    }

    /**
     * @covers ::addsContext
     */
    public function testAddsContext()
    {
        $this->assertFalse($this->matchContextCollection->addsContext());
    }
}
