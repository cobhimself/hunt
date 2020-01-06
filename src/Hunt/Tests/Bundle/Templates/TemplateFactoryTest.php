<?php

namespace Hunt\Bundle\Tests\Templates;

use Hunt\Bundle\Exceptions\InvalidTemplateException;
use Hunt\Bundle\Templates\ConfluenceWikiTemplate;
use Hunt\Bundle\Templates\ConsoleTemplate;
use Hunt\Bundle\Templates\TemplateFactory;
use Hunt\Tests\Bundle\Templates\TemplateTestCase;

/**
 * @codeCoverageIgnore
 *
 * @internal
 */
class TemplateFactoryTest extends TemplateTestCase
{
    /**
     * @dataProvider dataProviderForTestGet
     * @covers \Hunt\Bundle\Exceptions\InvalidTemplateException
     * @covers \Hunt\Bundle\Templates\TemplateFactory
     */
    public function testGet(string $type, array $expectations)
    {
        if (isset($expectations['exception'])) {
            $this->expectException($expectations['exception']['type']);
            $this->expectExceptionMessageRegExp($expectations['exception']['message']);
        }
        $template = TemplateFactory::get($type);

        $this->assertInstanceOf($expectations['instanceof'], $template);
    }

    public function dataProviderForTestGet(): array
    {
        return [
            'console template' => [
                'type'        => TemplateFactory::CONSOLE,
                'expectation' => [
                    'instanceof' => ConsoleTemplate::class,
                ],
            ],
            'confluence-wiki template' => [
                'type'        => TemplateFactory::CONFLUENCE_WIKI,
                'expectation' => [
                    'instanceof' => ConfluenceWikiTemplate::class,
                ],
            ],
            'non-existent template' => [
                'type'        => 'blah',
                'expectation' => [
                    'exception' => [
                        'type'    => InvalidTemplateException::class,
                        'message' => '/"blah" is not a valid template type./',
                    ],
                ],
            ],
        ];
    }
}
