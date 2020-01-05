<?php

namespace Hunt\Tests\Component;

use Hunt\Bundle\Exceptions\InvalidCommandArgumentException;
use Hunt\Component\Gatherer\GathererInterface;
use Hunt\Component\Gatherer\StringGatherer;
use Hunt\Component\Hunter;
use Hunt\Component\HunterArgs;
use Hunt\Tests\HuntTestCase;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 * @coversDefaultClass \Hunt\Component\Hunter
 * @covers ::__construct
 *
 * @uses \Hunt\Component\OutputStyler
 * @uses \Hunt\Bundle\Models\Result
 * @uses \Hunt\Bundle\Models\ResultCollection
 * @uses \Hunt\Bundle\Templates\AbstractTemplate
 * @uses \Hunt\Bundle\Templates\ConsoleTemplate
 * @uses \Hunt\Component\Gatherer\AbstractGatherer
 * @uses \Hunt\Component\Gatherer\StringGatherer
 * @codeCoverageIgnore
 */
class HunterTest extends HuntTestCase
{
    /**
     * @var Hunter
     */
    private $hunter;

    /**
     * @var OutputInterface
     */
    private $output;

    public function setUp()
    {
        $this->output = $this->getOutputMock();

        $this->hunter = new Hunter(
            $this->output,
            new ProgressBar($this->output)
        );
    }

    /**
     * @covers ::getBaseDir
     * @covers ::setBaseDir
     * @dataProvider dataProviderForTestSetBaseDir
     *
     * @param array $directories     an array of directory names
     * @param bool  $throwsException whether or not we should expect an exception to be thrown
     */
    public function testSetBaseDir(array $directories, bool $throwsException = false)
    {
        if ($throwsException) {
            $this->expectException(\InvalidArgumentException::class);
        }

        $this->hunter->setBaseDir($directories);
        $this->assertEquals($directories, $this->hunter->getBaseDir());
    }

    public function dataProviderForTestSetBaseDir(): array
    {
        return [
            'one directory' => [
                ['/tmp'],
            ],
            'multiple directories' => [
                ['/home', '/tmp'],
            ],
            'non-existent directory' => [
                ['/blah'],
                true,
            ],
        ];
    }

    /**
     * @covers ::isRecursive
     * @covers ::setRecursive
     */
    public function testSetRecursive()
    {
        //False by default
        $this->assertFalse($this->hunter->isRecursive());

        $this->hunter->setRecursive(true);
        $this->assertTrue($this->hunter->isRecursive());

        $this->hunter->setRecursive(false);
        $this->assertFalse($this->hunter->isRecursive());
    }

    /**
     * @covers ::getTerm
     * @covers ::setTerm
     */
    public function testSetTerm()
    {
        $this->hunter->setTerm('term');
        $this->assertEquals('term', $this->hunter->getTerm());
    }

    /**
     * @covers ::getOutput
     * @covers ::setOutput
     */
    public function testSetOutput()
    {
        $this->hunter->setOutput(new NullOutput());
        $this->assertInstanceOf(NullOutput::class, $this->hunter->getOutput());
    }

    /**
     * @covers ::getProgressBar
     * @covers ::setProgressBar
     */
    public function testSetProgressBar()
    {
        $progressBar = new ProgressBar($this->output, 777);
        $this->hunter->setProgressBar($progressBar);
        $this->assertEquals(777, $this->hunter->getProgressBar()->getMaxSteps());
    }

    /**
     * @covers ::isRegex
     * @covers ::setRegex
     */
    public function testSetRegex()
    {
        $this->hunter->setRegex(true);
        $this->assertTrue($this->hunter->isRegex());
    }

    /**
     * @dataProvider dataProviderForTestHunt
     * @covers ::gatherData
     * @covers ::generateTemplate
     * @covers ::getFileList()
     * @covers ::getGatherer
     * @covers ::getTerm
     * @covers ::hunt
     * @covers ::setBaseDir
     * @covers ::setExcludedTerms
     * @covers ::setGatherer
     * @covers ::setRecursive
     * @covers ::setTerm
     * @covers   \Hunt\Component\HunterFileListTraversable
     *
     * @uses \Hunt\Component\HunterArgs::getInvalidArgumentException()
     */
    public function testHunt(array $options, array $expectations)
    {
        $this->hunter
            ->setBaseDir($options[HunterArgs::DIR])
            ->setTerm($options[HunterArgs::TERM]);

        $exclude = [];
        if (isset($options[HunterArgs::EXCLUDE])) {
            $exclude = $options[HunterArgs::EXCLUDE];
            $this->hunter->setExcludedTerms($exclude);
        }

        if (isset($options[HunterArgs::RECURSIVE])) {
            $this->hunter->setRecursive($options[HunterArgs::RECURSIVE]);
        }

        $this->hunter->setGatherer(
            new StringGatherer($options[HunterArgs::TERM], $exclude)
        );

        if (isset($expectations['exception'])) {
            $expectation = $expectations['exception'];
            $this->expectException($expectation['type']);
            $this->expectExceptionMessageRegExp($expectation['message']);
        }

        $this->hunter->hunt();

        $output = $this->getOutputMockDisplay($this->output);

        if (isset($expectations['contains'])) {
            $expectation = $expectations['contains'];

            foreach ($expectation as $contains) {
                $this->assertContains($contains, $output);
            }
        }

        if (isset($expectations['notContains'])) {
            $expectation = $expectations['notContains'];

            foreach ($expectation as $notContains) {
                $this->assertNotContains($notContains, $output);
            }
        }
    }

    public function dataProviderForTestHunt(): array
    {
        $testFilesDir = realpath(__DIR__ . '/../TestFiles');

        return [
            'return zero search results' => [
                'options' => [
                    HunterArgs::DIR  => [$testFilesDir],
                    HunterArgs::TERM => self::SEARCH_TERM,
                ],
                'expectations' => [
                    'contains' => [
                        'Found 0 files containing the term ' . self::SEARCH_TERM,
                    ],
                ],
            ],
            'no term means error' => [
                'options' => [
                    HunterArgs::DIR  => [$testFilesDir . '/FakeClass.php'],
                    HunterArgs::TERM => '',
                ],
                'expectations' => [
                    'exception' => [
                        'type'    => InvalidCommandArgumentException::class,
                        'message' => '/A term must be specified/',
                    ],
                ],
            ],
            'single file, search: @deprecated' => [
                'options' => [
                    HunterArgs::DIR  => [$testFilesDir . '/FakeClass.php'],
                    HunterArgs::TERM => 'deprecated',
                ],
                'expectations' => [
                    'contains' => [
                        'Found 1 files containing the term deprecated',
                    ],
                ],
            ],
            'recurse, search: PHPUnit_, exclude: PHPUnit_Framework_MockObjects_MockObject' => [
                'options' => [
                    HunterArgs::DIR       => [$testFilesDir],
                    HunterArgs::RECURSIVE => true,
                    HunterArgs::TERM      => 'PHPUnit_',
                    HunterArgs::EXCLUDE   => ['PHPUnit_Framework_MockObjects_MockObject'],
                ],
                'expectations' => [
                    'contains' => [
                        'Found 2 files containing the term PHPUnit_',
                    ],
                    'notContains' => [
                        '*PHPUnit_*Framework_MockObjects_MockObject',
                    ],
                ],
            ],
        ];
    }

    /**
     * @covers ::getGatherer
     * @covers ::setGatherer
     */
    public function testSetGatherer()
    {
        $this->hunter->setGatherer(new StringGatherer('term'));
        /* @noinspection UnnecessaryAssertionInspection */
        $this->assertInstanceOf(GathererInterface::class, $this->hunter->getGatherer());
    }

    /**
     * @covers ::doTrimMatches
     * @covers ::setTrimMatches
     */
    public function testSetTrimMatches()
    {
        $this->assertFalse($this->hunter->doTrimMatches());

        $this->hunter->setTrimMatches(true);
        $this->assertTrue($this->hunter->doTrimMatches());

        $this->hunter->setTrimMatches(false);
        $this->assertFalse($this->hunter->doTrimMatches());
    }

    /**
     * @covers ::getExcludedTerms
     * @covers ::setExcludedTerms
     * @dataProvider dataProviderForTestSetExclude
     *
     * @param array $exclude an array of terms we want to exclude
     */
    public function testSetExclude(array $exclude)
    {
        $this->hunter->setExcludedTerms($exclude);
        $this->assertEquals($exclude, $this->hunter->getExcludedTerms());
    }

    public function dataProviderForTestSetExclude(): array
    {
        return [
            'nothing' => [
                [],
            ],
            'one term' => [
                ['term'],
            ],
            'multiple terms' => [
                ['term one', 'term two'],
            ],
        ];
    }
}
