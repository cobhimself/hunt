<?php

namespace Hunt\Bundle\Templates;

use Hunt\Bundle\Exceptions\InvalidTemplateException;

class TemplateFactory
{
    const CONSOLE = 'console';

    const CONFLUENCE_WIKI = 'confluence-wiki';

    const FILE_LIST = 'file-list';

    const DEFAULT = self::CONSOLE;

    /**
     * @var array An array of available templates
     */
    const TEMPLATE_LIST = [
        self::CONSOLE         => ConsoleTemplate::class,
        self::CONFLUENCE_WIKI => ConfluenceWikiTemplate::class,
        self::FILE_LIST       => FileListTemplate::class,
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
