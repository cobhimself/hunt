<?php

namespace Hunt\Bundle\Templates\Console;


use Hunt\Bundle\Models\Element\Formatter\FormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface ConsoleTemplateThemeInterface
{
    public function apply(OutputInterface $output);
    public function getFormatter(): FormatterInterface;
}