<?php

namespace Hunt\Tests;

use Hunt\Bundle\Models\Result;
use Hunt\Bundle\Models\ResultCollection;
use Hunt\Component\OutputStyler;
use PHPUnit\Framework\MockObject\MockObject;
use SplFileObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Finder\SplFileInfo;

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
            1 => 'this is line one and it has the ' . self::SEARCH_TERM . ' as well as ' . self::EXCLUDE_TERM,
            2 => 'this is line two',
            300 => 'this is line three hundred',
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
     * @param string $filename one of our RESULT_FILE_* constants
     */
    protected function getResultForFileConstant(string $filename): Result
    {
        if (!isset($this->resultMatchingLines[$filename])) {
            throw new \InvalidArgumentException('We do not have any resultMatchingLines data for filename ' . $filename);
        }

        $result = $this->getResultWithFileInfoMock(self::SEARCH_TERM, $filename);
        $result->setMatchingLines($this->resultMatchingLines[$filename]);

        return $result;
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
     * @param StreamOutput $output The stream output created by our getOutputMock method.
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
     * @param array $input
     * @param Command $command
     *
     * @return ArrayInput
     */
    protected function getInputObject(array $input, Command $command = null): ArrayInput
    {
        //We've got to be able to get the name of the command.
        if (!isset($input['command']) && null === $command) {
            throw new \LogicException(
                'HuntTestCase::getInputObject MUST have either a Command parameter'
                    . " or 'command' key in the input parameter"

            );
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
     */
    protected function getResultCollectionWithFileConstants(): ResultCollection
    {
        $results = [];

        foreach ($this->resultMatchingLines as $fileName => $lines) {
            $results[$fileName] = $this->getResultForFileConstant($fileName);
        }

        return new ResultCollection($results);
    }
}
