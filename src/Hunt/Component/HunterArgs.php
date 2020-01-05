<?php

namespace Hunt\Component;

use Hunt\Bundle\Command\HuntCommand;
use Hunt\Bundle\Exceptions\InvalidCommandArgumentException;
use Hunt\Component\Gatherer\GathererFactory;
use Hunt\Component\Gatherer\GathererInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class HunterArgs
{
    //Argument/Option names
    const DIR = 'dir';

    const TERM = 'term';

    const RECURSIVE = 'recursive';

    const EXCLUDE = 'exclude';

    const TRIM_MATCHES = 'trim-matches';

    const REGEX = 'regex';

    const PROGRESS_REDRAW_ANSI = 500;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var HuntCommand
     */
    private $cmd;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Hunter
     */
    private $hunter;

    /**
     * @var ProgressBar
     */
    private $progressBar;

    public function __construct(HuntCommand $cmd)
    {
        $this->cmd = $cmd;
    }

    public function configure()
    {
        $this->cmd->setDescription('Hunt down code and build a report.')
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
            )
            ->addOption(
                self::REGEX,
                '-E',
                null,
                'If given, the search term will be treated as if it were a regex expression'
            );
    }

    /**
     * Apply arguments and options to the hunter class.
     *
     * @param Hunter $hunter the hunter to setup
     */
    public function apply(Hunter $hunter, InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->hunter = $hunter;

        $this->setOutputStyles();
        $this->validateArguments();

        $hunter->setBaseDir($this->get(self::DIR))
            ->setRecursive($this->get(self::RECURSIVE))
            ->setTerm($this->get(self::TERM))
            ->setExcludedTerms($this->get(self::EXCLUDE))
            ->setTrimMatches($this->get(self::TRIM_MATCHES))
            ->setRegex($this->get(self::REGEX))
            ->setOutput($this->output)
            ->setProgressBar($this->getProgressBar())
            ->setGatherer($this->getGatherer());
    }

    /**
     * Get an argument or option if possible.
     *
     * @param string $key the argument/option name
     *
     * @return bool|string|string[]|null null if no option/argument can be found
     */
    public function get(string $key)
    {
        if ($this->input->hasArgument($key)) {
            return $this->getArgument($key);
        }

        if ($this->input->hasOption($key)) {
            return $this->getOption($key);
        }

        return null;
    }

    /**
     * @return bool|string|string[]|null
     */
    public function getOption(string $option)
    {
        return $this->input->getOption($option);
    }

    /**
     * @return string|string[]|null
     */
    public function getArgument(string $argument)
    {
        return $this->input->getArgument($argument);
    }

    private function setOutputStyles()
    {
        OutputStyler::applyFormat($this->output->getFormatter());
    }

    public function getProgressBar(): ProgressBar
    {
        if (null === $this->progressBar) {
            $this->setProgressBar($this->createProgressBar());
        }

        return $this->progressBar;
    }

    public function setProgressBar(ProgressBar $progressBar)
    {
        $this->progressBar = $progressBar;
    }

    private function createProgressBar(): ProgressBar
    {
        return OutputStyler::getProgressBar(
            $this->input,
            $this->output,
            $this->get('no-ansi') ? self::PROGRESS_REDRAW_ANSI : null
        );
    }

    private function getGatherer(): GathererInterface
    {
        $term = $this->get(self::TERM);
        $exclude = $this->get(self::EXCLUDE);

        $gathererType = (true === $this->get(self::REGEX))
            ? GathererFactory::GATHERER_REGEX
            : GathererFactory::GATHERER_STRING;

        $gatherer = GathererFactory::getByType($gathererType, $term, $exclude);
        $gatherer->setTrimMatchingLines($this->get(self::TRIM_MATCHES));

        return $gatherer;
    }

    public static function getInvalidArgumentException(string $argument, string $extraInfo = ''): InvalidCommandArgumentException
    {
        if ('' !== $extraInfo) {
            $extraInfo = ' ' . $extraInfo;
        }

        $messages = [
            self::DIR  => 'A valid directory or file to search through must be provided.' . $extraInfo,
            self::TERM => 'A term must be specified',
        ];

        $message = sprintf('Error with argument (%s): %s', $argument, $messages[$argument]);

        return new InvalidCommandArgumentException($message);
    }

    private function validateArguments()
    {
        $invalidArgument = null;

        foreach ($this->get(self::DIR) as $dir) {
            if (!file_exists($dir)) {
                $extra = sprintf('%s is not a directory or file', $dir);

                throw self::getInvalidArgumentException(self::DIR, $extra);
            }
        }

        $term = $this->get(self::TERM);
        if (empty($term)) {
            throw self::getInvalidArgumentException(self::TERM, 'Given term: ' . var_export($term, true));
        }
    }
}
