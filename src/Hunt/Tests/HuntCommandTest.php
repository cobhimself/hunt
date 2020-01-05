<?php

namespace Hunt\Tests;

use Hunt\Bundle\Command\HuntCommand;
use Hunt\Bundle\Exceptions\InvalidCommandArgumentException;
use Hunt\Component\Gatherer\StringGatherer;
use Hunt\Component\HunterArgs;
use InvalidArgumentException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 * @codeCoverageIgnore
 * @covers \Hunt\Component\OutputStyler
 * @coversDefaultClass \Hunt\Bundle\Command\HuntCommand
 */
class HuntCommandTest extends HuntTestCase
{
    const SEARCH_TERM = 'PHPUnit_';

    const EXCLUDE_TERM = ['PHPUnit_Framework_MockObjects_MockObject'];

    /**
     * @var HuntCommand
     */
    private $command;

    /**
     * @var CommandTester
     */
    private $tester;

    public function setUp()
    {
        $application = new Application(HuntCommand::CMD_NAME, HuntCommand::CMD_VERSION);
        $this->command = $application->add(new HuntCommand());
        //$application->setDefaultCommand(HuntCommand::CMD_NAME, true);

        $this->tester = new CommandTester($this->command);
    }

    /**
     * Test the execution of our Hunt Command.
     *
     * @covers ::configure()
     * @covers ::execute()
     * @covers ::getHunter()
     * @covers ::getHunterArgs()
     *
     * @covers \Hunt\Component\HunterArgs
     *
     * @uses \Hunt\Component\Hunter
     * @uses \Hunt\Bundle\Models\Result
     * @uses \Hunt\Bundle\Models\ResultCollection
     * @uses \Hunt\Bundle\Templates\AbstractTemplate
     * @uses \Hunt\Bundle\Templates\ConsoleTemplate
     * @uses \Hunt\Component\Gatherer\AbstractGatherer
     * @uses \Hunt\Component\Gatherer\StringGatherer
     * @uses \Hunt\Component\Gatherer\GathererFactory
     * @uses \Hunt\Component\HunterFileListTraversable
     *
     * @dataProvider dataProviderForExecute
     *
     * @param array $input        an array of input strings passed to our command
     * @param array $expectations an array of expectations.
     */
    public function testExecute(array $input, array $expectations)
    {
        $input = $this->prepareInputOptions($input);
        if (isset($expectations['exception'])) {
            $this->expectException($expectations['exception']['class']);
            $this->expectExceptionMessageRegExp($expectations['exception']['message']);
        }

        $this->assertInstanceOf(HunterArgs::class, $this->command->getHunterArgs());

        $this->tester->execute($input);
        $hunter = $this->command->getHunter();

        foreach ($expectations['options'] as $option => $value) {
            /** @var string $option */
            switch ($option) {
                case HunterArgs::DIR:
                    $actualValue = $hunter->getBaseDir();

                    break;
                case HunterArgs::TERM:
                    $actualValue = $hunter->getTerm();

                    break;
                case HunterArgs::EXCLUDE:
                    $actualValue = $hunter->getExcludedTerms();

                    break;
                case HunterArgs::TRIM_MATCHES:
                    $actualValue = $hunter->doTrimMatches();

                    break;
                case HunterArgs::REGEX:
                    $actualValue = $hunter->isRegex();

                    break;
                case HunterArgs::RECURSIVE:
                    $actualValue = $hunter->isRecursive();

                    break;
            }
            /* @var bool|array|string $actualValue */
            $this->assertEquals(
                $value,
                $actualValue,
                'Value for [' . $option . '] was expected to be ' . var_export($value, true)
            );
        }

        if (isset($expectations['gathererClass'])) {
            $this->assertInstanceOf($expectations['gathererClass'], $hunter->getGatherer());
        }

        // the output of the command in the console
        $output = $this->tester->getDisplay();
    }

    public function dataProviderForExecute(): array
    {
        $defaultDir = getcwd();
        $testFilesDirectory = __DIR__ . '/TestFiles/';

        return [
            'non-existent dir' => [
                'input' => [
                    HunterArgs::DIR  => ['/blah'],
                    HunterArgs::TERM => self::SEARCH_TERM,
                ],
                'expectations' => [
                    'exception' => [
                        'class'   => InvalidArgumentException::class,
                        'message' => '/A valid directory or file to search through must be provided/',
                    ],
                ],
            ],
            'no dir' => [
                'input' => [
                    HunterArgs::TERM => self::SEARCH_TERM,
                ],
                'expectations' => [
                    'options' => [
                        //We expect the current working directory to be the directory we search
                        HunterArgs::DIR          => [$defaultDir],
                        HunterArgs::REGEX        => false,
                        HunterArgs::EXCLUDE      => [],
                        HunterArgs::TRIM_MATCHES => false,
                        HunterArgs::TERM         => self::SEARCH_TERM,
                        HunterArgs::RECURSIVE    => false,
                    ],
                    'gathererInstance' => StringGatherer::class,
                ],
            ],
            'empty term' => [
                'input' => [
                    HunterArgs::DIR  => [$testFilesDirectory],
                    HunterArgs::TERM => '',
                ],
                'expectations' => [
                    'exception' => [
                        'class'   => InvalidCommandArgumentException::class,
                        'message' => '/A term must be specified/',
                    ],
                ],
            ],
            'term and dir' => [
                'input' => [
                    HunterArgs::DIR  => [$testFilesDirectory],
                    HunterArgs::TERM => self::SEARCH_TERM,
                    'no-ansi'        => true,
                ],
                'expectations' => [
                    'options' => [
                        HunterArgs::DIR          => [$testFilesDirectory],
                        HunterArgs::REGEX        => false,
                        HunterArgs::EXCLUDE      => [],
                        HunterArgs::TRIM_MATCHES => false,
                        HunterArgs::TERM         => self::SEARCH_TERM,
                        HunterArgs::RECURSIVE    => false,
                    ],
                    'gathererInstance' => StringGatherer::class,
                ],
            ],
            'all params' => [
                'input' => [
                    HunterArgs::DIR          => [$testFilesDirectory],
                    HunterArgs::REGEX        => false,
                    HunterArgs::EXCLUDE      => self::EXCLUDE_TERM,
                    HunterArgs::TRIM_MATCHES => true,
                    HunterArgs::TERM         => self::SEARCH_TERM,
                    HunterArgs::RECURSIVE    => true,
                ],
                'expectations' => [
                    'options' => [
                        HunterArgs::DIR          => [$testFilesDirectory],
                        HunterArgs::REGEX        => false,
                        HunterArgs::EXCLUDE      => self::EXCLUDE_TERM,
                        HunterArgs::TRIM_MATCHES => true,
                        HunterArgs::TERM         => self::SEARCH_TERM,
                        HunterArgs::RECURSIVE    => true,
                    ],
                ],
            ],
            'regex is true' => [
                'input' => [
                    HunterArgs::DIR   => [$testFilesDirectory],
                    HunterArgs::REGEX => true,
                    HunterArgs::TERM  => self::SEARCH_TERM,
                ],
                'expectations' => [
                    'options' => [
                        HunterArgs::DIR   => [$testFilesDirectory],
                        HunterArgs::REGEX => true,
                        HunterArgs::TERM  => self::SEARCH_TERM,
                    ],
                    'exception' => [
                        'class'   => \InvalidArgumentException::class,
                        'message' => '/Gatherer not implemented yet/',
                    ],
                ],
            ],
        ];
    }

    private function prepareInputOptions(array $input): array
    {
        static $keyTypeResolution = [
            'no-ansi' => 'option',
        ];
        $keysToRemove = [];

        /**
         * @var string
         * @var mixed  $value
         */
        foreach ($input as $key => $value) {
            if (!array_key_exists($key, $keyTypeResolution)) {
                $definition = $this->command->getDefinition();
                if ($definition->hasOption($key)) {
                    $keyTypeResolution[$key] = 'option';
                } elseif ($definition->hasArgument($key)) {
                    $keyTypeResolution[$key] = 'argument';
                }
            }
            if ('option' === $keyTypeResolution[$key]) {
                $input['--' . $key] = $value;
                $keysToRemove[] = $key;
            }
        }

        foreach ($keysToRemove as $key) {
            unset($input[$key]);
        }

        return $input;
    }
}
