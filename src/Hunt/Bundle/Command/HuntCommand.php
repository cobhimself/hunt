<?php

namespace Hunt\Bundle\Command;


use Hunt\Component\Gatherer\StringGatherer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Hunt\Component\Hunter;
use Symfony\Component\Console\Style\SymfonyStyle;

class HuntCommand extends Command
{
    const CMD_NAME = 'hunt';
    const CMD_VERSION = '1.0.0';

    //Argument/Option names
    const DIR = 'dir';
    const TERM = 'term';
    const RECURSIVE = 'recursive';
    const EXCLUDE = 'exclude';
    const TRIM_MATCHES = 'trim-matches';

    /**
     * @var string|null The default command name
     */
    protected static $defaultName = self::CMD_NAME;

    protected function configure()
    {
        $this->setDescription('Hunt down code and build a report.')
            ->setHelp('This command helps you find strings within files and report on where it is found.')
            ->addArgument(
                self::TERM,
                InputArgument::REQUIRED,
                'A string to hunt for.'
            )
            ->addArgument(
                self::DIR,
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'One or more directories to hunt for our TERM. If not specified, current working directory is used.',
                [getcwd()]
            )
            ->addOption(
                self::RECURSIVE,
                '-r',
                null,
                'Whether or not to recurse into the directory when searching'
            )
            ->addOption(
                self::EXCLUDE,
                '-e',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'A space separated list of search terms to exclude. Helpful when your term will return partial matches.',
                []
            )
            ->addOption(
                self::TRIM_MATCHES,
                '-t',
                null,
                'If given, matching lines will have whitespace removed from the beginning of the line.'
            );
    }

    /**
     * Execute our hunter command.
     *
     * @param InputInterface  $input The input object.
     * @param OutputInterface $output The output object.
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setOutputStyles($output);

        ProgressBar::setFormatDefinition('hunt', "%message% (%filename%)\n%current%/%max% [%bar%]");
        $progressBar = new ProgressBar($output);
        $progressBar->setFormat('hunt');

        if($input->getOption('no-ansi')) {
            $progressBar->setRedrawFrequency(500);
        }

        $hunter = new Hunter($output, $progressBar);

        $gatherer = new StringGatherer(
            $input->getArgument(self::TERM),
            $input->getOption(self::EXCLUDE)
        );

        $gatherer->setTrimMatchingLines($input->getOption(self::TRIM_MATCHES));

        $hunter->setBaseDir($input->getArgument(self::DIR))
            ->setRecursive($input->getOption(self::RECURSIVE))
            ->setTerm($input->getArgument(self::TERM))
            ->setExclude($input->getOption(self::EXCLUDE))
            ->setTrimMatches($input->getOption(self::TRIM_MATCHES))
            ->setGatherer($gatherer);

        $hunter->hunt();
    }

    /**
     * @param OutputInterface $output
     */
    private function setOutputStyles(OutputInterface $output)
    {
        $formatter = $output->getFormatter();
        $formatter->setStyle('info', new OutputFormatterStyle('green'));
        $formatter->setStyle('bold', new OutputFormatterStyle(null, null, ['bold']));
    }
}