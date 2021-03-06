<?php

namespace Hunt\Component;

use Hunt\Bundle\Command\HuntCommand;
use Hunt\Bundle\Exceptions\InvalidCommandArgumentException;
use Hunt\Bundle\Templates\TemplateFactory;
use Hunt\Bundle\Templates\TemplateInterface;
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

    const TEMPLATE = 'template';

    const EXCLUDE_DIRS = 'exclude-dir';

    const EXCLUDE_NAMES = 'exclude-name';

    const MATCH_PATH = 'match-path';

    const MATCH_NAME = 'match-name';

    const PROGRESS_REDRAW_ANSI = 500;

    const LIST_ONLY = 'list';

    /**
     * @since 1.5.0
     */
    const NUM_CONTEXT_LINES = 'context';

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
            )
            ->addOption(
                self::LIST_ONLY,
                '-l',
                null,
                sprintf(
                    'Only list file names with results. Alias for --%s. If given, the %s option is ignored.',
                    self::TEMPLATE . '=' . TemplateFactory::FILE_LIST,
                    TemplateFactory::FILE_LIST
                )
            )
            ->addOption(
                self::TEMPLATE,
                '-T',
                InputOption::VALUE_REQUIRED,
                'If provided, specifies the template to use. Must be one of: console, confluence-wiki, file-list.',
                'console'
            )
            ->addOption(
                self::EXCLUDE_DIRS,
                '-x',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'A directory name to exclude from our results; can include regex. Specify option multiple'
                    . 'times to exclude multiple directories',
                []
            )
            ->addOption(
                self::EXCLUDE_NAMES,
                '-X',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Exclude filenames with the given string; can include regex. Specify option multiple'
                . 'times to exclude multiple file names.',
                []
            )
            ->addOption(
                self::MATCH_PATH,
                '-m',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'A string to require be in the path of our results; can include regex. Specify multiple'
                . 'times to require multiple matches',
                []
            )
            ->addOption(
                self::MATCH_NAME,
                '-M',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'A string to require be in the file name of our results; can include regex. Specify multiple'
                . 'times to require multiple matches',
                []
            )
            ->addOption(
                self::NUM_CONTEXT_LINES,
                '-C',
                InputOption::VALUE_OPTIONAL,
                'The number of context lines before and after to provide for each match.'
            )
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
            ->setListOnly($this->get(self::LIST_ONLY))
            ->setTemplate($this->getTemplate())
            ->setExcludeDirs($this->get(self::EXCLUDE_DIRS))
            ->setExcludeFileNames($this->get(self::EXCLUDE_NAMES))
            ->setMatchPath($this->get(self::MATCH_PATH))
            ->setMatchName($this->get(self::MATCH_NAME))
            ->setGatherer($this->getGatherer())
            ->setNumContextLines($this->get(self::NUM_CONTEXT_LINES));
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

        return GathererFactory::getByType($gathererType, $term, $exclude);
    }

    public static function getInvalidArgumentException(string $argument, string $extraInfo = ''): InvalidCommandArgumentException
    {
        if ('' !== $extraInfo) {
            $extraInfo = ' ' . $extraInfo;
        }

        $messages = [
            self::DIR       => 'A valid directory or file to search through must be provided.' . $extraInfo,
            self::TERM      => 'A term must be specified',
            self::LIST_ONLY => 'The list option cannot be provided if a template has been specified.',
            self::REGEX     => 'Improperly formatted regex: ' . $extraInfo,
            self::NUM_CONTEXT_LINES => 'The number of context lines specified is invalid. It must be a number and greater than 0.',
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

        if (
            ('/' !== $term[0] || '/' !== $term[strlen($term) - 1])
            && $this->get(self::REGEX)
        ) {
            throw self::getInvalidArgumentException(self::REGEX, $term . ' does not appear to be a valid regex string. Make sure you start and end with /.');
        }

        $numContextLines = $this->get(self::NUM_CONTEXT_LINES);
        if (
            $numContextLines !== null && (!is_numeric($numContextLines) || $numContextLines <= 0)
        ) {
            throw self::getInvalidArgumentException(self::NUM_CONTEXT_LINES);
        }
    }

    private function getTemplate(): TemplateInterface
    {
        //See if we've specified we only want to se a list of files.
        $template = $this->get(self::LIST_ONLY)
            ? TemplateFactory::FILE_LIST
            : $this->get(self::TEMPLATE);

        return TemplateFactory::get($template);
    }
}
