<?php

namespace Hunt\Component;

/**
 * Helper which trims the same amount of leading strings off the left side of an array of strings.
 *
 * @since 1.5.0
 */
class Trimmer
{

    /**
     * @param string|array $lines A single line string or an array of line strings.
     */
    public static function getShortestLeadingSpaces($lines): int
    {
        $min = null;

        if (is_string($lines)) {
            $lines = [$lines];
        }

        foreach ($lines as $lineNum => $line) {
            //It's possible the line is empty. If that is the case, we do not want to perform any calculations on it.
            //We should trim any line breaks from it to confirm.
            if (!self::emptyLine($line)) {
                if (strpos($line, ' ') !== 0) {
                    return 0;
                }

                $trimmed = strlen($line) - strlen(ltrim($line, ' '));
                if ($min === null || $trimmed < $min) {
                    $min = $trimmed;
                }
            }
        }

        return $min;
    }

    /**
     * @param string|array $lines The lines to trim spaces off of. If an array, the trim is performed on each item.
     * @param int          $num   The number of spaces to trim from the beginning of the line.
     *
     * @return string|array An array is returned if an array was passed in, otherwise a trimmed string is returned.
     */
    public static function trim($lines, int $num)
    {
        if ($num === 0) {
            return $lines;
        }

        //If we're working with a single string, return the trimmed string.
        if (is_string($lines) && !self::emptyLine($lines)) {
            return substr($lines, $num);
        }

        //We've got an array. Trim each of the items.
        $lines = array_map(
            static function($line) use ($lines, $num) {
                return self::emptyLine($line) ? $line : substr($line, $num);
            },
            $lines
        );

        return $lines;
    }

    public static function emptyLine($line): bool
    {
        return str_replace("\n", '', $line) === '';
    }
}
