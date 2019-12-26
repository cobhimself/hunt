<?php

namespace Hunt\Bundle\Command;

use Hunt\Component\Gatherer\StringGatherer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Hunt\Component\Hunter;

class HuntCommand extends Command
{
    const CMD_NAME = 'hunt';
    const CMD_VERSION = '1.0.0';

    //Argument/Option names
    const DIR = 'dir';
    const TERM = 'term';
    const RECURSIVE = 'recursive';
    const EXCLUDE = 'exclude';

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
        $hunter = new Hunter($output);

        $hunter->setBaseDir($input->getArgument(self::DIR))
            ->setRecursive($input->getOption(self::RECURSIVE))
            ->setTerm($input->getArgument(self::TERM))
            ->setExclude($input->getOption(self::EXCLUDE))
            ->setGatherer(
                new StringGatherer(
                    $input->getArgument(self::TERM),
                    $input->getOption(self::EXCLUDE)
                )
            );

        $hunter->hunt();
    }
}
