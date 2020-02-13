<?php

namespace Hunt\Component\Gatherer;

/**
 * @since 1.4.0
 */
class RegexGatherer extends AbstractGatherer
{
    /**
     * Whether or not the given line matches.
     */
    public function lineMatches(string $line): bool
    {
        return !empty($line) && preg_match($this->term, $line);
    }

    /**
     * Perform the highlighting for the given line.
     */
    public function highlightLine(string $line, string $highlightStart = '', string $highlightEnd = ''): string
    {
        return preg_replace_callback(
            $this->term,
            static function ($matches) use ($highlightEnd, $highlightStart) {
                if (1 === count($matches)) {
                    return $highlightStart . $matches[0] . $highlightEnd;
                }

                return str_replace($matches[1], $highlightStart . $matches[1] . $highlightEnd, $matches[0]);
            },
            $line
        );
    }
}
