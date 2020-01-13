<?php

namespace Hunt\Tests\Component;

use Hunt\Bundle\Exceptions\InvalidTemplateException;
use Hunt\Bundle\Templates\TemplateFactory;
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
 * @uses \Hunt\Bundle\Templates\FileListTemplate
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
     * @covers ::getBaseDir
     * @covers ::getExcludeDirs
     * @covers ::getExcludedTerms
     * @covers ::getExcludeFileNames
     * @covers ::getFileList()
     * @covers ::getGatherer
     * @covers ::getMatchName
     * @covers ::getMatchPath
     * @covers ::getTemplate
     * @covers ::getTerm
     * @covers ::hunt
     * @covers ::isListOnly
     * @covers ::isRecursive
     * @covers ::setBaseDir
     * @covers ::setExcludeDirs
     * @covers ::setExcludedTerms
     * @covers ::setExcludeFileNames
     * @covers ::setGatherer
     * @covers ::setListOnly
     * @covers ::setMatchName
     * @covers ::setMatchPath
     * @covers ::setRecursive
     * @covers ::setTemplate
     * @covers ::setTerm
     * @covers \Hunt\Bundle\Exceptions\InvalidTemplateException
     * @covers   \Hunt\Component\HunterFileListTraversable
     *
     * @uses \Hunt\Component\HunterArgs::getInvalidArgumentException()
     * @uses \Hunt\Bundle\Templates\TemplateFactory
     */
    public function testHunt(array $options, array $expectations)
    {
        if (isset($expectations['exception'])) {
            $expectation = $expectations['exception'];
            $this->expectException($expectation['type']);
            $this->expectExceptionMessageRegExp($expectation['message']);
        }

        if (isset($options['get_template_before_set'])) {
            $this->hunter->getTemplate();
        }

        //Set our template to default to the console template unless we've specified we do not want the template
        //to be specified for us.
        if (!isset($options[HunterArgs::TEMPLATE]) && !isset($options['do_not_set_default_template'])) {
            $options[HunterArgs::TEMPLATE] = TemplateFactory::CONSOLE;
        }

        if (isset($options[HunterArgs::TEMPLATE])) {
            //Convert our template value string into an actual template so we can set it directly
            $options[HunterArgs::TEMPLATE] = TemplateFactory::get($options[HunterArgs::TEMPLATE]);
        }

        $this->setOptionsOnHunter($options);

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
            'no term' => [
                'options' => [
                    HunterArgs::DIR  => [$testFilesDir],
                ],
                'expectations' => [
                    'exception' => [
                        'type'    => \InvalidArgumentException::class,
                        'message' => '/A term must be specified/',
                    ],
                ],
            ],
            'no base directory' => [
                'options' => [
                    HunterArgs::TERM => 'test',
                ],
                'expectations' => [
                    'exception' => [
                        'type'    => \InvalidArgumentException::class,
                        'message' => '/A valid directory or file to search through must/',
                    ],
                ],
            ],
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
                        'type'    => \InvalidArgumentException::class,
                        'message' => '/Term cannot be empty/',
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
            'bad template' => [
                'options' => [
                    HunterArgs::DIR      => [$testFilesDir],
                    HunterArgs::TERM     => self::SEARCH_TERM,
                    HunterArgs::TEMPLATE => 'bad-template',
                ],
                'expectations' => [
                    'exception' => [
                        'type'    => InvalidTemplateException::class,
                        'message' => '/"bad-template" is not a valid template type./',
                    ],
                ],
            ],
            'attempt to get template before set' => [
                'options' => [
                    HunterArgs::DIR           => [$testFilesDir],
                    HunterArgs::TERM          => self::SEARCH_TERM,
                    'get_template_before_set' => true,
                ],
                'expectations' => [
                    'exception' => [
                        'type'    => \LogicException::class,
                        'message' => '/Cannot get template because it has not been set/',
                    ],
                ],
            ],
            'exclude dir1' => [
                'options' => [
                    HunterArgs::DIR          => [$testFilesDir],
                    HunterArgs::TERM         => 'PHPUnit_',
                    HunterArgs::RECURSIVE    => true,
                    HunterArgs::EXCLUDE_DIRS => ['dir1'],
                ],
                'expectations' => [
                    'contains' => [
                        'Found 1 files containing the term PHPUnit_',
                        'dir2/FakeClassTest.php',
                    ],
                    'notContains' => [
                        'dir1',
                    ],
                ],
            ],
            'exclude dir regex' => [
                'options' => [
                    HunterArgs::DIR          => [$testFilesDir],
                    HunterArgs::TERM         => 'PHPUnit_',
                    HunterArgs::RECURSIVE    => true,
                    HunterArgs::EXCLUDE_DIRS => ['/dir.*/'],
                ],
                'expectations' => [
                    'contains' => [
                        'Found 0 files containing the term PHPUnit_',
                    ],
                    'notContains' => [
                        'dir1',
                        'dir2/FakeClassTest.php',
                    ],
                ],
            ],
            'exclude file name *.txt' => [
                'options' => [
                    HunterArgs::DIR           => [$testFilesDir],
                    HunterArgs::TERM          => 'PHPUnit_',
                    HunterArgs::RECURSIVE     => true,
                    HunterArgs::EXCLUDE_NAMES => ['*.txt'],
                ],
                'expectations' => [
                    'contains' => [
                        'Found 1 files containing the term PHPUnit_',
                    ],
                    'notContains' => [
                        'plain.txt',
                    ],
                ],
            ],
            'exclude file name plain*' => [
                'options' => [
                    HunterArgs::DIR           => [$testFilesDir],
                    HunterArgs::TERM          => 'PHPUnit_',
                    HunterArgs::RECURSIVE     => true,
                    HunterArgs::EXCLUDE_NAMES => ['/.*lain.*/'],
                ],
                'expectations' => [
                    'contains' => [
                        'Found 1 files containing the term PHPUnit_',
                    ],
                    'notContains' => [
                        'plain.txt',
                    ],
                ],
            ],
            'require dir1 in path' => [
                'options' => [
                    HunterArgs::DIR           => [$testFilesDir],
                    HunterArgs::TERM          => 'PHPUnit_',
                    HunterArgs::RECURSIVE     => true,
                    HunterArgs::MATCH_PATH    => ['dir1'],
                ],
                'expectations' => [
                    'contains' => [
                        'Found 1 files containing the term PHPUnit_',
                    ],
                    'notContains' => [
                        'FakeClassTest.php',
                    ],
                ],
            ],
            'match deep folder path with globs' => [
                'options' => [
                    HunterArgs::DIR           => [$testFilesDir],
                    HunterArgs::TERM          => 'Purple Monkey!',
                    HunterArgs::RECURSIVE     => true,
                    HunterArgs::MATCH_PATH    => ['/.*\/test\/.*/'],
                ],
                'expectations' => [
                    'contains' => [
                        'dir2/dir3/test/FakeClassTest.php',
                        'Found 1 files containing the term Purple Monkey!',
                    ],
                    'notContains' => [
                        'dir1',
                    ],
                ],
            ],
            'match file name' => [
                'options' => [
                    HunterArgs::DIR           => [$testFilesDir],
                    HunterArgs::TERM          => 'PHPUnit_',
                    HunterArgs::RECURSIVE     => true,
                    HunterArgs::MATCH_NAME    => ['*txt'],
                ],
                'expectations' => [
                    'contains' => [
                        'plain.txt',
                        'Found 1 files containing the term PHPUnit_',
                    ],
                    'notContains' => [
                        'dir2',
                    ],
                ],
            ],
            'match file name regex' => [
                'options' => [
                    HunterArgs::DIR           => [$testFilesDir],
                    HunterArgs::TERM          => 'FakeClass',
                    HunterArgs::RECURSIVE     => true,
                    HunterArgs::MATCH_NAME    => ['/FakeClass.*\.php/'],
                ],
                'expectations' => [
                    'contains' => [
                        '*FakeClass*Test',
                        'Found 3 files containing the term FakeClass',
                    ],
                    'notContains' => [
                        'plain.txt',
                    ],
                ],
            ],
            'list only' => [
                'options' => [
                    HunterArgs::DIR               => [$testFilesDir],
                    HunterArgs::TERM              => 'FakeClass',
                    HunterArgs::RECURSIVE         => true,
                    HunterArgs::LIST_ONLY         => true,
                    'do_not_set_default_template' => true,
                ],
                'expectations' => [
                    'contains' => [
                        'Found 4 files containing the term FakeClass',
                    ],
                    'notContains' => [
                        ':',
                    ],
                ],
            ],
            'list only even when template provided' => [
                'options' => [
                    HunterArgs::DIR       => [$testFilesDir],
                    HunterArgs::TERM      => 'FakeClass',
                    HunterArgs::RECURSIVE => true,
                    HunterArgs::LIST_ONLY => true,
                    HunterArgs::TEMPLATE  => TemplateFactory::CONSOLE,
                ],
                'expectations' => [
                    'contains' => [
                        'Found 4 files containing the term FakeClass',
                    ],
                    'notContains' => [
                        ':',
                    ],
                ],
            ],
            'list files using list template' => [
                'options' => [
                    HunterArgs::DIR       => [$testFilesDir],
                    HunterArgs::TERM      => 'FakeClass',
                    HunterArgs::RECURSIVE => true,
                    HunterArgs::TEMPLATE  => TemplateFactory::FILE_LIST,
                ],
                'expectations' => [
                    'contains' => [
                        'Found 4 files containing the term FakeClass',
                    ],
                    'notContains' => [
                        ':',
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

    /**
     * Take the options given to us for the test and set them on our hunter object.
     */
    private function setOptionsOnHunter(array $options)
    {
        //Go through each of our options and set them
        foreach ($options as $option => $value) {
            $this->callHunterSetter($this->hunter, $option, $value);
        }

        $this->hunter->setGatherer(new StringGatherer($this->hunter->getTerm(), $this->hunter->getExcludedTerms()));
    }
}
