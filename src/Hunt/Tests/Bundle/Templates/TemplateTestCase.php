<?php

namespace Hunt\Tests\Bundle\Templates;


use Hunt\Bundle\Models\ResultCollection;
use Hunt\Bundle\Templates\TemplateInterface;
use Hunt\Tests\HuntTestCase;

class TemplateTestCase extends HuntTestCase
{
    /**
     * @var TemplateInterface
     */
    protected $template;

    /**
     * Get the result collection for our template test.
     *
     * @return ResultCollection
     */
    protected function getResultCollection(): ResultCollection
    {
        return $this->getResultCollectionWithFileConstants();
    }
}
