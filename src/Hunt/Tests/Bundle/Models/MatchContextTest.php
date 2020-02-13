<?php

namespace Hunt\Tests\Bundle\Models;

use Hunt\Bundle\Models\MatchContext\MatchContext;
use Hunt\Tests\HuntTestCase;

/**
 * @coversDefaultClass \Hunt\Bundle\Models\MatchContext\MatchContext
 * @codeCoverageIgnore
 */
class MatchContextTest extends HuntTestCase
{

    /**
     * @covers ::setAfter
     * @covers ::getAfter
     * @covers ::setBefore
     * @covers ::getBefore
     * @covers ::__construct
     */
    public function testMatchContext()
    {
        $before = ['one', 'two', 'three'];
        $after = ['five', 'six', 'seven'];

        $matchContext = new MatchContext($before, $after);

        $this->assertEquals($before, $matchContext->getBefore());
        $this->assertEquals($after, $matchContext->getAfter());
    }
}
