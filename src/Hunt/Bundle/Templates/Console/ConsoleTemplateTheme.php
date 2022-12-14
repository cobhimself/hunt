<?php


namespace Hunt\Bundle\Templates\Console;


use Hunt\Bundle\Models\Element\ContextLineNumber;
use Hunt\Bundle\Models\Element\ContextSplit;
use Hunt\Bundle\Models\Element\Line\ContextLine;
use Hunt\Bundle\Models\Element\LineNumber;
use Hunt\Bundle\Models\Element\ResultFilePath;
use Hunt\Bundle\Models\Line\Line;
use Hunt\Bundle\Models\Line\ParsedLine;
use Hunt\Bundle\Models\Line\Parts\Excluded;
use Hunt\Bundle\Models\Line\Parts\Match;
use Hunt\Bundle\Models\Line\Parts\Normal;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConsoleTemplateTheme
{
    public function apply(OutputInterface $output)
    {
        $this->definitions = $this->resolver->resolve($this->definitions);

        $formatter = $output->getFormatter();

        foreach ($this->definitions as $key => list($foreground, $background, $options)) {
            $formatter->setStyle($key, new OutputFormatterStyle($foreground, $background, $options));
        }
    }
}