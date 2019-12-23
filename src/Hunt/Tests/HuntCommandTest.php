<?php


namespace Hunt\Tests;

use Symfony\Component\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Hunt\Bundle\Command\HuntCommand;

class HuntCommandTest extends KernelTestCase
{
    /**
     * @var Command
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

    public function executeDataProvider(): array
    {
        return [
            'return all files' => [
                // prefix the key with two dashes when passing options,
                // e.g: '--some-option' => 'option_value',
                'options' => [
                    '--' . HuntCommand::RECURSIVE => true,
                    HuntCommand::TERM => 'PHPUnit_',
                    HuntCommand::DIR => [realpath('src/Hunt/Tests/TestFiles')],
                ],
                'expectations' => [

                ]
            ]
        ];
    }

    /**
     * Test the execution of our Hunt Command.
     *
     * @dataProvider executeDataProvider
     *
     * @param array $options      An array of command line arguments to be sent to the command.
     * @param array $expectations An array of expectations we have for the command.
     */
    public function testExecute(array $options, array $expectations)
    {
        $this->tester->execute($options);

        // the output of the command in the console
        $output = $this->tester->getDisplay();

        $this->assertEquals();
    }
}
