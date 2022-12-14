<?php

namespace Hunt\Tests\Component\Gatherer;

use Hunt\Component\Gatherer\GathererFactory;
use Hunt\Component\Gatherer\RegexGatherer;
use Hunt\Component\Gatherer\StringGatherer;
use Hunt\Tests\HuntTestCase;
use InvalidArgumentException;

/**
 * @codeCoverageIgnore
 *
 * @internal
 */
class GathererFactoryTest extends HuntTestCase
{
    /**
     * @covers \Hunt\Component\Gatherer\GathererFactory
     * @dataProvider dataProviderForTestGetByType
     */
    public function testGetByType(int $type, string $className)
    {
        if (-1 === $type) {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('Unknown gatherer type: -1');
        }

        $this->assertInstanceOf(
            $className,
            GathererFactory::getByType($type, self::SEARCH_TERM, [])
        );
    }

    public function dataProviderForTestGetByType(): array
    {
        return [
            'string gatherer' => [
                'type'      => GathererFactory::GATHERER_STRING,
                'className' => StringGatherer::class,
            ],
            'regex gatherer' => [
                'type'      => GathererFactory::GATHERER_REGEX,
                'className' => RegexGatherer::class,
            ],
            'unknown gatherer' => [
                'type'      => -1,
                'className' => '',
            ],
        ];
    }
}
