<?php

namespace Hunt\Tests;

use Hunt\Bundle\Models\Result;
use Hunt\Bundle\Models\ResultCollection;
use PHPUnit\Framework\TestCase;
use SplFileObject;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @internal
 */
class HuntTestCase extends TestCase
{
    const SEARCH_TERM = 'searchTerm';

    const EXCLUDE_TERM = 'searchTermExclude';

    //We will use different characters for the beginning and end to confirm we are highlighting correctly.
    const HIGHLIGHT_START = '*';

    const HIGHLIGHT_END = '#';

    const RESULT_FILE_ONE = 'this/is/a/file/name/one';

    const RESULT_FILE_TWO = 'this/is/a/file/name/two';

    const RESULT_FILE_THREE = 'this/is/a/file/name/three';

    const EXPECTED_FILE_NAME = 'this/is/a/file/name.php';

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

        return $output;
    }

    /**
     * Return a ResultCollection made up of our default matching line data.
     * j.
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
