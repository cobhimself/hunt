<?php

namespace Hunt\Tests\Component\Gatherer;

use Hunt\Component\Gatherer\GathererFactory;
use Hunt\Component\Gatherer\StringGatherer;
use Hunt\Tests\HuntTestCase;

/**
 * @codeCoverageIgnore
 */
class GathererFactoryTest extends HuntTestCase
{

    /**
     * @covers \Hunt\Component\Gatherer\GathererFactory
     * @dataProvider dataProviderForTestGetByType
     */
    public function testGetByType(int $type, string $className)
    {
        if ($type === -1) {
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage('Unknown gatherer type: -1');
        } elseif ($type === GathererFactory::GATHERER_REGEX) {
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage('Gatherer not implemented yet.');
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
                'type' => GathererFactory::GATHERER_STRING,
                'className' => StringGatherer::class
            ],
            'regex gatherer' => [
                'type' => GathererFactory::GATHERER_REGEX,
                'className' => ''
            ],
            'unknown gatherer' => [
                'type' => -1,
                'className' => ''
            ]
        ];
    }
}
