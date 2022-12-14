<?php

namespace Hunt\Tests\Component\MatchContext;

use Hunt\Bundle\Models\Element\Line\LineFactory;
use Hunt\Bundle\Models\MatchContext\DummyMatchContextCollection;
use Hunt\Component\MatchContext\ContextCollector;
use Hunt\Component\MatchContext\ContextCollectorFactory;
use Hunt\Component\MatchContext\DummyContextCollector;
use PHPUnit\Framework\TestCase;

/**
 * @codeCoverageIgnore
 * @covers \Hunt\Component\MatchContext\ContextCollector
 * @uses \Hunt\Bundle\Models\MatchContext\MatchContext
 * @uses \Hunt\Bundle\Models\MatchContext\MatchContextCollection
 */
class ContextCollectorTest extends TestCase
{
    /**
     * @covers \Hunt\Bundle\Models\MatchContext\MatchContextCollectionFactory::get
     * @covers \Hunt\Component\MatchContext\ContextCollectorFactory::get
     */
    public function testContextCollector()
    {
        $lines = [
            //Let's make it so we get a full list of before context but only two after context
            'zero',
            'one',
            'two',
            'three',
            '*four*',
            'five',
            'six',
            //What happens when we have a match right after another?
            '*seven',
            //This match will have it's maximum after context
            '*eight',
            'nine',
            'ten',
            'eleven',
            'twelve',
            'thirteen',
            'fourteen',
            //This match will have maximum before and after context
            '*fifteen*',
            'sixteen',
            'seventeen',
            'eighteen',
            'nineteen',
            //What about every other?
            '*twenty*',
            'twenty one',
            '*twenty two',
            'twenty three',
            //This match will have zero after context
            '*twenty four',
        ];

        $collector = ContextCollectorFactory::get(3);

        foreach ($lines as $i => $line) {
            $isMatch = (strpos($line, '*') !== false);
            $collector->addLine($line, $isMatch);
        }

        $collector->finalize();
        $collection = $collector->getContextCollection();

        $expectations = [
            4 => [
                'before' => [1 => 'one', 2 => 'two', 3 => 'three'],
                'after'  => [5 => 'five', 6 => 'six']
            ],
            7 => [
                'before' => [5 => 'five', 6 => 'six'],
                'after' => []
            ],
            8 => [
                'before' => [],
                'after' => [9 => 'nine', 10 => 'ten', 11 => 'eleven']
            ],
            15 => [
                'before' => [12 => 'twelve', 13 => 'thirteen', 14 => 'fourteen'],
                'after' => [16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen']
            ],
            20 => [
                'before' => [17 => 'seventeen', 18 => 'eighteen', 19 => 'nineteen'],
                'after' => [21 => 'twenty one']
            ],
            22 => [
                'before' => [21 => 'twenty one'],
                'after' => [23 => 'twenty three']
            ],
            24 => [
                'before' => [23 => 'twenty three'],
                'after' => []

            ],
        ];

        foreach ($expectations as $matchingLine => $expect) {
            $before = $expect['before'];
            $after = $expect['after'];
            $matchContext = $collection->getContextForLine($matchingLine);
            $this->assertEquals($before, $matchContext->getBefore());
            $this->assertEquals($after, $matchContext->getAfter());
        }
    }

    /**
     * @covers \Hunt\Component\MatchContext\ContextCollectorFactory::get
     * @covers \Hunt\Component\MatchContext\DummyContextCollector
     * @covers \Hunt\Bundle\Models\MatchContext\DummyMatchContextCollection
     */
    public function testDummyContextCollector()
    {
        $collector = ContextCollectorFactory::get(0);
        $this->assertInstanceOf(DummyContextCollector::class, $collector);
        $this->assertInstanceOf(DummyMatchContextCollection::class, $collector->getContextCollection());
    }

    /**
     * @uses \Hunt\Bundle\Models\MatchContext\DummyMatchContextCollection::getCollectionSize()
     * @uses \Hunt\Bundle\Models\MatchContext\MatchContextCollectionFactory::get
     */
    public function testNoContextLines()
    {
        $collector = new ContextCollector(0);
        $collector->addLine(LineFactory::getLine(1, 'blah'), false);
        $collector->addLine(LineFactory::getLine(2, 'match'), true);
        $collector->addLine(LineFactory::getLine(3, 'bleh'), false);
        $collector->finalize();
        $this->assertEquals(0, $collector->getContextCollection()->getCollectionSize());
    }
}
