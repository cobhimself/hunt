<?php

namespace Hunt\Bundle\Models\Element\Formatter;


use Hunt\Bundle\Exceptions\FormatterException;
use Hunt\Bundle\Models\Element\Line\ContextLineNumber;
use Hunt\Bundle\Models\Element\ContextSplit;
use Hunt\Bundle\Models\Element\Line\ContextLine;
use Hunt\Bundle\Models\Element\Line\LineNumber;
use Hunt\Bundle\Models\Element\ResultFilePath;
use Hunt\Bundle\Models\Element\Line\Line;
use Hunt\Bundle\Models\Element\Line\ParsedLine;
use Hunt\Bundle\Models\Element\Line\Parts\Excluded;
use Hunt\Bundle\Models\Element\Line\Parts\Match;
use Hunt\Bundle\Models\Element\Line\Parts\Normal;

class ColoredConsoleFormatter extends Formatter
{
    use TagWrapElementTrait;

    public static $elementTags = [
        Line::class              => 'line',
        LineNumber::class        => 'lineNumber',
        ParsedLine::class        => 'parsedLine',
        ContextLine::class       => 'contextLine',
        ContextLineNumber::class => 'contextLineNumber',
        ContextSplit::class      => 'contextSplit',
        ResultFilePath::class    => 'resultFilePath',
        Excluded::class          => 'excluded',
        Match::class             => 'match',
        Normal::class            => 'normal',
    ];

    /**
     * ConsoleFormatter constructor.
     *
     * @throws FormatterException
     */
    public function __construct()
    {
        foreach (self::$elementTags as $element => $tag) {
            try {
                $this->wrapElement($element, $tag);
            } catch (FormatterException $e) {
                throw new FormatterException(
                    sprintf('Unable to setup wrap for element %s', $element),
                    null,
                    $e
                );
            }
        }

        //Add a colon after the the line number
        $originalAfter = $this->getAfterContentForElement(LineNumber::class);
        $this->setAfterContentForElement(LineNumber::class, ': ' . $originalAfter);
    }

    public function getTagForElement(string $elementClass)
    {
        return self::$elementTags[$elementClass] ?? '';
    }
}