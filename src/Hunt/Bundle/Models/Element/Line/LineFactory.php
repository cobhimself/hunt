<?php


namespace Hunt\Bundle\Models\Element\Line;


use Hunt\Bundle\Exceptions\LineFactoryException;
use Hunt\Bundle\Exceptions\LineProcessFlowChangeException;
use Hunt\Bundle\Models\Element\Line\Parts\PartsCollection;

class LineFactory
{
    /**
     * @throws LineFactoryException
     */
    public static function getParsed(Line $line, PartsCollection $parts): ParsedLine
    {
        $parsedLine = new ParsedLine($line->getContent());
        $parsedLine->setLineNumber($line->getLineNumber());

        try {
            $parsedLine->setParts($parts);
        } catch (LineProcessFlowChangeException $e) {
            throw new LineFactoryException('Cannot get the parsed version of a line.', null, $e);
        }

        $parsedLine->setTranslate($line->getTranslate())
        ->setContainsExcludedContent($line->getContainsExcludedContent());

        return $parsedLine;
    }

    public static function getLine(string $lineNum, string $content): Line
    {
        $line = new Line($content);
        $line->setLineNumber($lineNum);

        return $line;
    }

    public static function getContextLineFromLine(Line $line): ContextLine
    {
        $contextLine = new ContextLine($line->getContent());
        $contextLine->setLineNumber($line->getLineNumber());

        return $contextLine;
    }
}