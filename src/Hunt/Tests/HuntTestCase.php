<?php

namespace Hunt\Tests;

use Hunt\Bundle\Models\Element\Line\Line;
use Hunt\Bundle\Models\Element\Line\LineFactory;
use Hunt\Bundle\Models\Element\Line\Parts\Excluded;
use Hunt\Bundle\Models\Element\Line\Parts\Match;
use Hunt\Bundle\Models\Element\Line\Parts\Normal;
use Hunt\Bundle\Models\Element\Line\Parts\PartsCollection;
use Hunt\Bundle\Models\MatchContext\MatchContext;
use Hunt\Bundle\Models\MatchContext\MatchContextCollectionFactory;
use Hunt\Bundle\Models\MatchContext\MatchContextCollectionInterface;
use Hunt\Bundle\Models\Result;
use Hunt\Bundle\Models\ResultCollection;
use Hunt\Component\Hunter;
use Hunt\Component\HunterArgs;
use Hunt\Component\OutputStyler;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use SplFileObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Finder\SplFileInfo;
use const PHP_EOL;

/**
 * @internal
 * @codeCoverageIgnore
 */
class HuntTestCase extends KernelTestCase
{
    const SEARCH_TERM = 'searchTerm';

    const EXCLUDE_TERM = 'searchTermExclude';

    //We will use different characters for the beginning and end to confirm we are highlighting correctly.
    const HIGHLIGHT_START = '*';

    const HIGHLIGHT_END = '#';

    const RESULT_FILE_ONE = 'this/is/a/file/name/one';

    const RESULT_FILE_TWO = 'this/is/a/file/name/two';

    const RESULT_FILE_THREE = 'this/is/a/file/name/three';

    const HUNTER_SETTER_MAP = [
        HunterArgs::TERM          => 'setTerm',
        HunterArgs::DIR           => 'setBaseDir',
        HunterArgs::EXCLUDE_DIRS  => 'setExcludeDirs',
        HunterArgs::EXCLUDE_NAMES => 'setExcludeFileNames',
        HunterArgs::EXCLUDE       => 'setExcludedTerms',
        HunterArgs::RECURSIVE     => 'setRecursive',
        HunterArgs::MATCH_PATH    => 'setMatchPath',
        HunterArgs::MATCH_NAME    => 'setMatchName',
        HunterArgs::LIST_ONLY     => 'setListOnly',
        HunterArgs::TEMPLATE      => 'setTemplate',
        HunterArgs::NUM_CONTEXT_LINES => 'setNumContextLines',
        HunterArgs::TRIM_MATCHES => 'setTrimMatches',
    ];

    const HUNTER_GETTER_MAP = [
        HunterArgs::TERM          => 'getTerm',
        HunterArgs::DIR           => 'getBaseDir',
        HunterArgs::EXCLUDE_DIRS  => 'getExcludeDirs',
        HunterArgs::EXCLUDE_NAMES => 'getExcludeFileNames',
        HunterArgs::EXCLUDE       => 'getExcludedTerms',
        HunterArgs::RECURSIVE     => 'isRecursive',
        HunterArgs::MATCH_PATH    => 'getMatchPath',
        HunterArgs::MATCH_NAME    => 'getMatchName',
        HunterArgs::LIST_ONLY     => 'isListOnly',
        HunterArgs::TEMPLATE      => 'getTemplate',
        HunterArgs::NUM_CONTEXT_LINES      => 'getNumContextLines',
        HunterArgs::TRIM_MATCHES => 'doTrimMatchces',
    ];

    /**
     * An array of result files with lines we are using as "matches".
     *
     * We use it a lot when creating expectations for Result objects.
     *
     * @var array
     */
    protected $resultMatchingLines = [
        self::RESULT_FILE_ONE => [
            1 => 'this is line one',
            2 => 'this is line two',
            3 => 'line three has the ' . self::SEARCH_TERM,
        ],
        self::RESULT_FILE_TWO => [
            1 => 'this is line one',
            2 => 'this is line two with the ' . self::SEARCH_TERM,
            3 => 'this is line three with ' . self::SEARCH_TERM . 'Ok',
        ],
        self::RESULT_FILE_THREE => [
            1   => 'this is line one and it has the ' . self::SEARCH_TERM . ' as well as ' . self::EXCLUDE_TERM,
            2   => 'this is line two',
            300 => 'this is line three hundred',
        ],
    ];

    /**
     * An array of Line elements generated based on our $resultMatchingLines property.
     *
     * @var Line[]
     */
    protected $finalResultMatchingLines;

    /**
     * Contains data necessary to construct MatchContext objects for each of the result file constants.
     *
     * NOTE: We're assuming each of the lines in our resultMatchingLines is a match even though they really aren't
     * matches based on any search term. Do not try to make sense of them being "matches".
     */
    protected $resultContextLines = [
        self::RESULT_FILE_ONE => [
            1 => [
                'before' => [],
                'after' => [
                    2 => 'this is line two',
                    3 => 'line three has the ' . self::SEARCH_TERM
                ],
            ],
            2 => [
                'before' => [
                    1 => 'this is line one'
                ],
                'after' => [
                    3 => 'line three has the ' . self::SEARCH_TERM
                ],
            ],
            3 => [
                'before' => [
                    1 => 'this is line one',
                    2 => 'this is line two'
                ],
                'after' => [],
            ],
        ],
        self::RESULT_FILE_TWO => [
            1 => [
                'before' => [],
                'after' => [
                    2 => 'this is line two with the ' . self::SEARCH_TERM,
                    3 => 'this is line three with ' . self::SEARCH_TERM . 'Ok'
                ],
            ],
            2 => [
                'before' => [
                    1 => 'this is line one'
                ],
                'after' => [
                    3 => 'this is line three with ' . self::SEARCH_TERM . 'Ok'
                ],
            ],
            3 => [
                'before' => [
                    1 => 'this is line one',
                    2 => 'this is line two with the ' . self::SEARCH_TERM,
                ],
                'after' => [],
            ],
        ],
        self::RESULT_FILE_THREE => [
            1 => [
                'before' => [],
                'after' => [
                    2 => 'this is line two',
                    300 => 'this is line three hundred'
                ],
            ],
            2 => [
                'before' => [
                    1   => 'this is line one and it has the ' . self::SEARCH_TERM . ' as well as ' . self::EXCLUDE_TERM,
                ],
                'after' => [
                    300 => 'this is line three hundred'
                ],
            ],
            300 => [
                'before' => [
                    1   => 'this is line one and it has the ' . self::SEARCH_TERM . ' as well as ' . self::EXCLUDE_TERM,
                    2 => 'this is line two',
                ],
                'after' => [],
            ],
        ],
    ];

    protected function getSplFileObjectMock(): SplFileObject
    {
        $fileMock = $this->createTestProxy(SplFileObject::class, ['php://memory']);
        $fileMock->method('setFlags')
            ->willReturn('');

        return $fileMock;
    }

    protected function getSplFileInfoMock(SplFileObject $fileMock = null): SplFileInfo
    {
        if (null === $fileMock) {
            $fileMock = $this->getSplFileObjectMock();
        }

        $fileInfoMock = $this->createMock(SplFileInfo::class);
        $fileInfoMock->method('openFile')
            ->willReturn($fileMock);

        return $fileInfoMock;
    }

    /**
     * Return a Result mock after creating an SplFileInfo mock for it to use.
     *
     * @return MockObject|Result
     */
    protected function getResultWithFileInfoMock(string $searchTerm, string $fileName): Result
    {
        return new Result(
            $searchTerm,
            $fileName,
            $this->getSplFileInfoMock()
        );
    }

    /**
     * Get a result object with matching lines set for the given filename.
     *
     * @param string $filename     one of our RESULT_FILE_* constants
     * @param bool   $contextLines Whether or not to add context line data.
     */
    protected function getResultForFileConstant(string $filename, bool $contextLines = false): Result
    {
        if (!isset($this->resultMatchingLines[$filename])) {
            throw new InvalidArgumentException('We do not have any resultMatchingLines data for filename ' . $filename);
        }

        $result = $this->getResultWithFileInfoMock(self::SEARCH_TERM, $filename);
        $result->setMatchingLines($this->getResultMatchingLines($filename));

        $result->setContextCollection($this->getMatchContextCollectionForFileConstant($filename, $contextLines));

        return $result;
    }

    /**
     * Clear our final result matching lines cache.
     */
    protected function resetFinalResultMatchingLines()
    {
        $this->finalResultMatchingLines = [];
    }

    /**
     * Get an array of matching lines associated with the given filename constant.
     *
     * @param string $filename
     * @return Line[]
     */
    protected function getResultMatchingLines(string $filename): array
    {
        $this->finalResultMatchingLines = $this->finalResultMatchingLines ?? [];

        if (!array_key_exists($filename, $this->finalResultMatchingLines)) {
            $lines = $this->resultMatchingLines[$filename];

            $finalLines = [];

            foreach ($lines as $lineNum => $lineContent) {
                $finalLines[$lineNum] = $this->getLine($lineNum, $lineContent);
            }

            $this->finalResultMatchingLines[$filename] = $finalLines;
        }

        return $this->finalResultMatchingLines[$filename];
    }

    /**
     * Return a MatchContextCollection for the given filename constant.
     *
     * @param string $fileName one of our RESULT_FILE_* constants
     */
    protected function getMatchContextCollectionForFileConstant(string $fileName, bool $real): MatchContextCollectionInterface
    {
        if (!isset($this->resultContextLines[$fileName])) {
            throw new InvalidArgumentException('We do not have any resultContextLines data for filename ' . $fileName);
        }

        $collection = MatchContextCollectionFactory::get($real);

        foreach ($this->resultContextLines[$fileName] as $matchingLineNum => $contextLines) {
            $context = new MatchContext($contextLines['before'], $contextLines['after']);
            $collection->addContext($matchingLineNum, $context);
        }

        return $collection;
    }

    /**
     * Get a mock for our OutputInterface needs.
     */
    protected function getOutputMock(): StreamOutput
    {
        $output = new StreamOutput(fopen('php://memory', 'wb', false));
        $output->setDecorated(false);

        //Need to style the output so our color tags are processed correctly.
        OutputStyler::applyFormat($output->getFormatter());

        return $output;
    }

    /**
     * Get the output collected within our output mock.
     *
     * @param StreamOutput $output the stream output created by our getOutputMock method
     *
     * @return string The final output mock output
     */
    protected function getOutputMockDisplay(StreamOutput $output): string
    {
        rewind($output->getStream());

        $display = stream_get_contents($output->getStream());
        $display = str_replace(PHP_EOL, "\n", $display);

        return $display;
    }

    /**
     * Get an input object for the given command with the given input from array.
     *
     * @param Command $command
     */
    protected function getInputObject(array $input, Command $command = null): ArrayInput
    {
        //We've got to be able to get the name of the command.
        if (!isset($input['command']) && null === $command) {
            throw new LogicException('HuntTestCase::getInputObject MUST have either a Command parameter' . " or 'command' key in the input parameter");
        }

        // set the command name automatically if the input was not given the application requires
        // this argument and no command name was passed
        if (!isset($input['command'])
            && (null !== $application = $command->getApplication())
            && $application->getDefinition()->hasArgument('command')) {
            $input = array_merge(['command' => $command->getName()], $input);
        }

        return new ArrayInput($input);
    }

    /**
     * Return a ResultCollection made up of our default matching line data.
     *
     * NOTE: the matching line data doesn't make sense because not each line contains the search term. It's just data
     * we are forcing to be "matching".
     *
     * @param bool $contextLines Whether or not to add context line data.
     */
    protected function getResultCollectionWithFileConstants(bool $contextLines = false): ResultCollection
    {
        $results = new ResultCollection();

        foreach ($this->resultMatchingLines as $fileName => $lines) {
            $results->addResult($this->getResultForFileConstant($fileName, $contextLines));
        }

        return $results;
    }

    /**
     * Calls the setter for a specific Hunter argument.
     *
     * @param string $arg   the HunterArgs constant for the argument we want to set
     * @param mixed  $value the value we want to set for the argument
     */
    protected function callHunterSetter(Hunter $hunter, string $arg, $value)
    {
        //Don't call what we don't have.
        if (!array_key_exists($arg, self::HUNTER_SETTER_MAP)) {
            return;
        }

        $this->callHunterFunc($hunter, self::HUNTER_SETTER_MAP[$arg], [$value]);
    }

    /**
     * Calls the getter for a specific Hunter argument.
     *
     * @param string $arg the HunterArgs constant for the argument we want to get
     */
    protected function callHunterGetter(Hunter $hunter, string $arg)
    {
        //Don't call what we don't have.
        if (!array_key_exists($arg, self::HUNTER_GETTER_MAP)) {
            return;
        }

        $this->callHunterFunc($hunter, self::HUNTER_GETTER_MAP[$arg]);
    }

    /**
     * Calls the provided function on the given Hunter object.
     */
    protected function callHunterFunc(Hunter $hunter, string $func, array $args = [])
    {
        call_user_func_array([$hunter, $func], $args);
    }

    /**
     * Return a parts collection for our test purposes.
     *
     * @param array $parts An array of parts with character identifiers for the type alongside a number.
     *
     * @example We can create a parts collection of a Match, Normal, and Excluded parts like so:
     *
     * $this->generateLineParts([
     *     'm1' => 'this will be the content for our Match part',
     *     'n1' => 'this will be a normal part',
     *     'e1' => 'this will be an excluded part,
     *     'n2' => 'another normal part but with a 2 so it can be indexed in the array'
     *     'm2' => 'look, another match!',
     *     'e2' => 'maybe exclude something else'
     * ]);
     *
     * This would be similar to the following:
     *
     * $parts = new PartsCollection();
     * $parts[] = new Match('this will be the content for our Match part');
     * $parts[] = new Normal('this will be a normal part');
     * $parts[] = new Excluded('this will be an excluded part');
     * $parts[] = new Normal('another normal part but with a 2 so it can be indexed in the array');
     * $parts[] = new Match('look, another match!');
     * $parts[] = new Excluded('maybe exclude something else');
     *
     */
    protected function getLinePartsForTest(array $parts): PartsCollection
    {
        $finalParts = new PartsCollection();

        foreach ($parts as $type => $content) {
            $firstLetter = $type[0];
            if ($firstLetter === 'n') {
                $finalParts[] = new Normal($content);
            } else if ($firstLetter === 'm') {
                $finalParts[] = new Match($content);
            } else if ($firstLetter === 'e') {
                $finalParts[] = new Excluded($content);
            }
        }

        return $finalParts;
    }

    /**
     * Quickly get a line element.
     */
    protected function getLine(int $lineNumber, string $content): Line
    {
        return LineFactory::getLine($lineNumber, $content);
    }

    /**
     * Quickly create an array of Line elements.
     *
     * @param int $numLines The number of lines to create.
     *
     * @return Line[]
     */
    protected function buildLineArray(int $numLines): array
    {
        $lines = [];

        for ($i =0; $i < $numLines; $i += 1) {
            $lines[$i] = LineFactory::getLine($i, 'line ' . $i);
        }

        return $lines;
    }
}
