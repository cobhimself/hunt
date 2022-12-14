<?php

namespace Hunt\Bundle\Models\Element\Line;


use Hunt\Bundle\Exceptions\LineProcessFlowChangeException;
use Hunt\Bundle\Models\Element\Formatter\FormatterInterface;
use Hunt\Bundle\Models\Element\Line\Parts\Excluded;
use Hunt\Bundle\Models\Element\Line\Parts\Normal;
use Hunt\Bundle\Models\Element\Line\Parts\PartsCollection;
use Hunt\Component\StringSearchWalker;

class ParsedLine extends Line
{

    protected function getInitialState(): int
    {
        return self::STATE_TERMS_EXCLUDED;
    }

    /**
     * Add our excluded terms back to the Normal parts of our line.
     *
     * At this point, we have Normal and Match parts in our line. However, we still need to put our Excluded parts back.
     * There is no need to go through our Match parts but it's possible our Normal parts will have instances of
     * our excluded terms.
     *
     * @throws LineProcessFlowChangeException
     */
    public function addExcludedTermsBack()
    {
        $this->setState(self::STATE_PARTS_FINALIZED);

        //No need to add excluded content back if we never had anything excluded.
        if (!$this->getContainsExcludedContent()) {
            return;
        }

        if (count($this->translate) > 1) {
            //Always sort the translate array by the length of placeholders (longest to shortest) so replacements
            //happen correctly.
            uasort($this->translate, static function ($a, $b) {
                $lenA = strlen($a);
                $lenB = strlen($b);

                if ($lenA === $lenB) {
                    return 0;
                }

                return ($lenA > $lenB) ? -1 : 1;
            });
        }

        foreach($this->translate as $excludedTerm => $placeholder) {
            $finalParts = new PartsCollection();

            foreach ($this->parts as $part) {
                if ($part instanceof Normal) {
                    $walker = new StringSearchWalker($part->getContent(), $placeholder);
                    foreach ($walker as $beforeContent) {
                        $finalParts->add(new Normal($beforeContent));
                        $finalParts->add(new Excluded($excludedTerm));
                    }
                    $finalParts->add(new Normal($walker->tail()));
                } else {
                    $finalParts->add($part);
                }
            }

            $this->parts = $finalParts;
        }
    }

    /**
     * Get the line formatted according to the given formatter.
     *
     * @param FormatterInterface $formatter The line formatter to use on this line.
     *
     * @return string The final formatted line.
     */
    public function getFormatted(FormatterInterface $formatter): string
    {
        return $formatter->getFormattedLine($this);
    }
}