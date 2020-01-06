<?php

namespace Hunt\Bundle\Templates;

use Hunt\Bundle\Exceptions\InvalidTemplateException;

class TemplateFactory
{
    const CONSOLE = 'console';

    const CONFLUENCE_WIKI = 'confluence-wiki';

    /**
     * @var array An array of available templates
     */
    const TEMPLATE_LIST = [
        self::CONSOLE         => ConsoleTemplate::class,
        self::CONFLUENCE_WIKI => ConfluenceWikiTemplate::class,
    ];

    public static function get(string $type): TemplateInterface
    {
        if (!array_key_exists($type, self::TEMPLATE_LIST)) {
            throw new InvalidTemplateException($type);
        }

        $template = self::TEMPLATE_LIST[$type];

        return new $template();
    }
}
