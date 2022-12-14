<?php

namespace Hunt\Tests\Component\Gatherer;

use Hunt\Bundle\Models\Element\Line\ParsedLine;
use Hunt\Component\Gatherer\AbstractGatherer;
use RuntimeException;

/**
 * @internal
 * @coversDefaultClass \Hunt\Component\Gatherer\AbstractGatherer
 * @codeCoverageIgnore
 */
class AbstractGathererTest extends GathererTestCase
{
    public function setUp()
    {
        $this->gatherer = $this->getMockForAbstractClass(
            AbstractGatherer::class,
            [self::SEARCH_TERM, [self::EXCLUDE_TERM]]
        );
        $this->gatherer
            ->method('getParsedLine')
            ->willReturn(new ParsedLine());
    }


    public function testGather()
    {

    }

    public function testSetAndGetNumContextLines()
    {
        $this->gatherer->setNumContextLines(1);
        $this->assertTrue($this->gatherer->getNumContextLines());
    }

    public function testSetAndDoTrimMatchingLines()
    {
        $this->gatherer->setTrimMatchingLines(true);
        $this->assertTrue($this->gatherer->doTrimMatchingLines());

        $this->gatherer->setTrimMatchingLines(false);
        $this->assertFalse($this->gatherer->doTrimMatchingLines());
    }

    public function testParseResultLines()
    {
        $result = $this->getResultForFileConstant(self::RESULT_FILE_ONE);
    }
}
