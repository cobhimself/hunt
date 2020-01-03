<?php

/** @noinspection DisconnectedForeachInstructionInspection */

namespace Hunt\Component;

use Hunt\Bundle\Models\Result;
use Hunt\Bundle\Models\ResultCollection;
use Hunt\Bundle\Templates\ConsoleTemplate;
use Hunt\Component\Gatherer\GathererInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class Hunter
{
    /**
     * The base directory we will be hunting within.
     *
     * @var array
     */
    private $baseDir;

    /**
     * Whether or not we want to search recursively.
     *
     * @var bool
     */
    private $recurse = false;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var string
     */
    private $term = '';

    /**
     * @var array
     */
    private $excludeTerms = [];

    /**
     * @var ResultCollection a collection of Hunter Results
     */
    private $found;

   /**
     * The gatherer this hunter uses.
     *
     * @var GathererInterface
     */
    private $gatherer;

    /**
     * Whether or not the trim whitespace from the matching lines.
     *
     * @var bool
     */
    private $trimMatches = false;

    /**
     * A progress bar we can use to update our progress.
     *
     * @var ProgressBar|null
     */
    private $progressBar;

    /**
     * Whether or not our search term is a regex term.
     *
     * @var bool
     */
    private $regex;

    /**
     * Hunter constructor.
     */
    public function __construct(OutputInterface $output = null, ProgressBar $progressBar = null)
    {
        $this->output = $output;
        $this->progressBar = $progressBar;
    }

    public function setOutput(OutputInterface $output): Hunter
    {
        $this->output = $output;

        return $this;
    }

    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    public function setProgressBar(ProgressBar $progressBar): Hunter
    {
        $this->progressBar = $progressBar;

        return $this;
    }

    public function getProgressBar(): ProgressBar
    {
        return $this->progressBar;
    }

    /**
     * Set the base directory where we we perform our search.
     *
     * @param array[string] $baseDir The base directory to search
     */
    public function setBaseDir(array $baseDir = null): Hunter
    {
        foreach ($baseDir as $dir) {
            if (!file_exists($dir)) {
                throw new \InvalidArgumentException('The given base directory cannot be found: ' . $dir);
            }
        }

        $this->baseDir = $baseDir;

        return $this;
    }

    /**
     * Whether or not to recursively search within our base directory.
     *
     * @param bool $recurse if true, we will recurse; otherwise, we'll stay within the base directory
     */
    public function setRecursive(bool $recurse): Hunter
    {
        $this->recurse = $recurse;

        return $this;
    }

    /**
     * Specify the term we are hunting for.
     */
    public function setTerm(string $term): Hunter
    {
        $this->term = $term;

        return $this;
    }

    /**
     * Hunt through our files for the given search strings.
     */
    public function hunt()
    {
        if (empty($this->getTerm())) {
            throw HunterArgs::getInvalidArgumentException(HunterArgs::TERM);
        }

        $this->output->writeln('<info>Starting the hunt for <bold>' . $this->term . '</bold></info>');

        $this->getFileList();

        //No need to continue if we didn't find anything.
        if (count($this->found) > 0) {
            $this->gatherData();
            $this->generateTemplate();
        }
    }

    /**
     * @param array[string] $excludeTerms An array of terms to exclude
     */
    public function setExcludedTerms(array $excludeTerms): Hunter
    {
        $this->excludeTerms = $excludeTerms;

        return $this;
    }

    /**
     * Performs the initial search for files which contain the term.
     */
    private function getFileList()
    {
        $found = [];

        $fileList = new HunterFileListTraversable($this->baseDir, $this->term, $this->recurse);

        $this->progressBar->setMessage('Finding files with matches');
        $this->progressBar->setMessage('...', 'filename');

        $this->progressBar->start();

        /** @var Result $result */
        foreach ($fileList as $result) {
            $this->progressBar->advance();
            $this->progressBar->setMessage($result->getFileName(), 'filename');
            $found[] = $result;
        }

        $this->progressBar->finish();
        $this->progressBar->clear();

        $this->found = new ResultCollection($found);

        //Did we even find anything?
        $this->output->writeln(
            sprintf(
                'Found <bold>%d</bold> files containing the term <bold>%s</bold>.',
                count($this->found),
                $this->term
            )
        );
    }

    /**
     * Build the final result set.
     *
     * Goes through each result and finds the lines which match our options.
     */
    private function gatherData()
    {
        $this->progressBar->setMessage('Building Results');
        $this->progressBar->start(count($this->found));

        //Sort our result collection
        $this->found->sortByFilename();

        /** @var Result $result */
        foreach ($this->found as $result) {
            $this->progressBar->setMessage($result->getFilename(), 'filename');
            $this->progressBar->advance();

            //Filter our result set. If no matches exist afterwards, we'll squash it.
            $containsResults = $this->getGatherer()->gather($result);

            if (!$containsResults) {
                $this->progressBar->advance(-1);
            }
        }

        //Remove any results from our list which are empty.
        $this->found->squashEmptyResults();

        $this->progressBar->finish();
    }

    private function generateTemplate()
    {
        $template = new ConsoleTemplate($this->found, $this->output);
        $template->setGatherer($this->gatherer);

        $this->progressBar->start(count($this->found));
        $this->progressBar->setMessage('Rendering template');

        foreach ($this->found as $result) {
            $this->progressBar->setMessage($result->getFilename(), 'filename');
            $this->progressBar->advance();
            $template->renderResult($result);
        }

        $this->progressBar->finish();
        $this->progressBar->clear();

        $this->output->writeln($template->getOutput());
    }

    /**
     * Set the Gatherer this Hunt is going to use to find the search term within the files.
     */
    public function setGatherer(GathererInterface $gatherer): Hunter
    {
        $this->gatherer = $gatherer;

        return $this;
    }

    /**
     * Set whether or not we want to trim matching lines.
     */
    public function setTrimMatches(bool $trimMatches): Hunter
    {
        $this->trimMatches = $trimMatches;

        return $this;
    }

    public function getBaseDir(): array
    {
        return $this->baseDir;
    }

    public function isRecursive(): bool
    {
        return $this->recurse;
    }

    public function getTerm(): string
    {
        return $this->term;
    }

    public function getGatherer(): GathererInterface
    {
        return $this->gatherer;
    }

    /**
     * Whether or not we should trim the spaces at the beginning of our result matches.
     */
    public function doTrimMatches(): bool
    {
        return $this->trimMatches;
    }

    public function getExcludedTerms(): array
    {
        return $this->excludeTerms;
    }

    public function setRegex(bool $regex): Hunter
    {
        $this->regex = $regex;

        return $this;
    }

    public function isRegex(): bool
    {
        return $this->regex;
    }
}
