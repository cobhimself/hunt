<?php


namespace Hunt\Bundle\Templates\Console;


use Hunt\Bundle\Models\Element\Line\ContextLineNumber;
use Hunt\Bundle\Models\Element\ContextSplit;
use Hunt\Bundle\Models\Element\Formatter\ColoredConsoleFormatter;
use Hunt\Bundle\Models\Element\Formatter\FormatterInterface;
use Hunt\Bundle\Models\Element\Line\ContextLine;
use Hunt\Bundle\Models\Element\Line\LineNumber;
use Hunt\Bundle\Models\Element\ResultFilePath;
use Hunt\Bundle\Models\Element\Line\Line;
use Hunt\Bundle\Models\Element\Line\ParsedLine;
use Hunt\Bundle\Models\Element\Line\Parts\Excluded;
use Hunt\Bundle\Models\Element\Line\Parts\Match;
use Hunt\Bundle\Models\Element\Line\Parts\Normal;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

class BaseConsoleTemplateTheme implements ConsoleTemplateThemeInterface
{
    /**
     * @var FormatterInterface
     */
    private $formatter;

    /**
     * @var array
     */
    private $definitions = [
        Line::class              => ['white', 'black', []],
        LineNumber::class        => ['white', 'black', []],
        ParsedLine::class        => ['white', 'black', []],
        ContextLine::class       => ['default', 'black', []],
        ContextLineNumber::class => ['default', 'black', []],
        ContextSplit::class      => ['default', 'black', []],
        ResultFilePath::class    => ['white', 'black', ['bold']],
        Excluded::class          => ['default', 'black', []],
        Match::class             => ['yellow', 'black', ['bold']],
        Normal::class            => ['default', 'black', []],
    ];

    public function apply(OutputInterface $output)
    {
        $outputFormatter = $output->getFormatter();
        $outputFormatter->setDecorated(true);
        $huntFormatter = $this->getFormatter();

        foreach ($this->definitions as $class => list($foreground, $background, $option)) {
            $name = $huntFormatter->getTagForElement($class);
            $outputFormatter->setStyle($name, new OutputFormatterStyle($foreground, $background, $option));
        }
    }

    public function getFormatter(): FormatterInterface
    {
        if (null === $this->formatter) {
            $this->formatter = new ColoredConsoleFormatter();
        }

        return $this->formatter;
    }
}