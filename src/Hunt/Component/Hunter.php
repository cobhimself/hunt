<?php


namespace Hunt\Component;

use Hunt\Bundle\Models\Result;
use Hunt\Bundle\Models\ResultCollection;
use Hunt\Bundle\Templates\ConsoleTemplate;
use Hunt\Component\Gatherer\GathererInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class Hunter
{
    /**
     * The base directory we will be hunting within.
     *
     * @var string
     */
    private $baseDir;

    /**
     * Whether or not we want to search recursively
     * @var bool
     */
    private $recurse;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var string
     */
    private $term;

    /**
     * @var array
     */
    private $excludeTerms;

    /**
     * @var ResultCollection A collection of Hunter Results.
     */
    private $found;
    /**
     * The gatherer this hunter uses.
     *
     * @var GathererInterface
     */
    private $gatherer;

    /**
     * Hunter constructor.
     *
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Set the base directory where we we perform our search.
     *
     * @param array[string] $baseDir The base directory to search.
     *
     * @return Hunter
     */
    public function setBaseDir(array $baseDir): Hunter
    {
        $this->baseDir = $baseDir;

        return $this;
    }

    /**
     * Whether or not to recursively search within our base directory.
     *
     * @param bool $recurse If true, we will recurse; otherwise, we'll stay within the base directory.
     *
     * @return Hunter
     */
    public function setRecursive(bool $recurse): Hunter
    {
        $this->recurse = $recurse;

        return $this;
    }

    /**
     * Specify the term we are hunting for.
     *
     * @param string $term
     *
     * @return Hunter
     */
    public function setTerm(string $term): Hunter
    {
        $this->term = $term;

        return $this;
    }

    /**
     * Hunt through our files for the given search strings
     */
    public function hunt()
    {
        $this->output->writeln('Starting the hunt for ' . $this->term);

        $this->getFileList();
        $this->gatherData();
        $this->generateTemplate();
    }

    /**
     * @param array[string] $excludeTerms An array of terms to exclude.
     *
     * @return Hunter
     */
    public function setExclude(array $excludeTerms): Hunter
    {
        $this->excludeTerms = $excludeTerms;

        return $this;
    }

    /**
     * Performs the initial search for files which contain the term
     */
    private function getFileList()
    {
        $found = [];
        $finder = new Finder();
        $finder->files()->in($this->baseDir);

        if (!$this->recurse) {
            $finder->depth('== 0');
        }

        $this->output->writeln('Searching for term: ' . $this->term);

        $finder->contains($this->term);

        $progress = new ProgressBar($this->output);
        $progress->start();

        foreach ($finder as $file) {
            /** @noinspection DisconnectedForeachInstructionInspection */
            $progress->advance();
            $path = $file->getRelativePath();

            $result = new Result($this->term, $path, $file);
            $result->setTrimResultSpacing();

            $found[$path] = $result;
        }

        $progress->finish();

        $this->output->writeln('');

        $this->found = new ResultCollection($found);

        $this->output->writeln(sprintf('Found %d files containing the term %s.', count($this->found), $this->term));
    }

    /**
     * Build the final result set.
     *
     * Goes through each result and finds the lines which match our options.
     */
    private function gatherData()
    {
        $this->output->writeln('Building Results');

        //Sort our result collection
        $this->found->sortByFilename();

        $progress = new ProgressBar($this->output, count($this->found));
        $progress->start();

        foreach ($this->found as $result) {
            /** @noinspection DisconnectedForeachInstructionInspection */
            $progress->advance();

            //Filter our result set. If no matches exist afterwards, we'll squash it.
            $containsResults = $this->gatherer->gather($result);

            if (!$containsResults) {
                $progress->advance(-1);
            }
        }

        //Remove any results from our list which are empty.
        $this->found->squashEmptyResults();

        $progress->finish();
    }

    private function generateTemplate()
    {
        $template = new ConsoleTemplate($this->found, $this->output);

        $progress = new ProgressBar($this->output, count($this->found));
        $progress->start();

        foreach ($this->found as $result) {
            /** @noinspection DisconnectedForeachInstructionInspection */
            $progress->advance();
            $template->renderResult($result);
        }
        $progress->finish();

        $this->output->writeln('');
        $this->output->writeln($template->getOutput());
    }

    /**
     * Set the Gatherer this Hunt is going to use to find the search term within the files.
     *
     * @param GathererInterface $gatherer
     *
     * @return Hunter
     */
    public function setGatherer(GathererInterface $gatherer): Hunter
    {
        $this->gatherer = $gatherer;

        return $this;
    }
}
