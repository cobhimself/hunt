<?php


namespace Hunt\Component;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OutputStyler
{
    public static function applyFormat(OutputFormatterInterface $formatter)
    {
        $formatter->setStyle(
            'info',
            new OutputFormatterStyle('green')
        );

        $formatter->setStyle(
            'bold',
            new OutputFormatterStyle(null, null, ['bold'])
        );
    }

    /**
     * @param int $nonAnsiRedrawFrequency if the output is non-ansi, the redraw frequency value to set
     *                                    on the progress bar.
     * @return ProgressBar
     */
    public static function getProgressBar(
        InputInterface $input,
        OutputInterface $output,
        int $nonAnsiRedrawFrequency = null
    ): ProgressBar {
        ProgressBar::setFormatDefinition('hunt', "%message% (%filename%)\n%current%/%max% [%bar%]");
        $progressBar = new ProgressBar($output);
        $progressBar->setFormat('hunt');

        if ($input->getOption('no-ansi')) {
            $progressBar->setRedrawFrequency($nonAnsiRedrawFrequency);
        }

        return $progressBar;
    }
}