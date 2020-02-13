<?php

namespace Hunt\Tests\Bundle\Templates;

use Hunt\Bundle\Models\ResultCollection;
use Hunt\Bundle\Templates\TemplateInterface;
use Hunt\Tests\HuntTestCase;

/**
 * @internal
 * @codeCoverageIgnore
 */
class TemplateTestCase extends HuntTestCase
{
    /**
     * @var TemplateInterface
     */
    protected $template;

    /**
     * Get the result collection for our template test.
     * @param bool $includeContextLines Whether or not to include context data.
     */
    protected function getResultCollection(bool $includeContextLines = false): ResultCollection
    {
        return $this->getResultCollectionWithFileConstants($includeContextLines);
    }
}
