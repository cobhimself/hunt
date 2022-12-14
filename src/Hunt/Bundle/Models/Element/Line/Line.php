<?php


namespace Hunt\Bundle\Models\Element\Line;


use Hunt\Bundle\Exceptions\LineProcessFlowChangeException;

class Line extends AbstractLine
{
    protected $translate = [];

    protected function getInitialState(): int
    {
        return self::STATE_FRESH;
    }

    /**
     * @param array $exclude
     * @return Line
     *
     * @throws LineProcessFlowChangeException
     */
    public function removeExcludedTerms(array $exclude): Line
    {
        $this->setState(self::STATE_TERMS_EXCLUDED);

        $this->setContainsExcludedContent(false);

        //Exit early if we can.
        if (0 === count($exclude)) {
            return $this;
        }

        //We could use regex but it's possible the complexity would cause the search to take a long time. Therefore,
        //we are going to replace our exclude terms with placeholders, find our matches, and then put our
        //exclude terms back.

        $placeholder = self::getPlaceholder();
        $counter = 0;

        //We need to build a translation between our exclude terms and the placeholders
        $translate = [];
        foreach ($exclude as $toExclude) {
            ++$counter;
            $translate[$toExclude] = str_repeat($placeholder, $counter);
        }

        $this->setTranslate($translate);

        $translated = strtr($this->content, $translate);
        if ($translated !== $this->content) {
            $this->content = $translated;
            $this->setContainsExcludedContent(true);
        }

        return $this;
    }

    public static function getPlaceholder(): string
    {
        return "\u{731f}\u{5e2b}";
    }

    public function setTranslate(array $translate): LineInterface
    {
        $this->translate = $translate;

        return $this;
    }

    public function getTranslate(): array
    {
        return $this->translate;
    }
}