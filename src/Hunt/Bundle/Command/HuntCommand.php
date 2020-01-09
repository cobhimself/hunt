<?php

namespace Hunt\Bundle\Command;

use Hunt\Component\Hunter;
use Hunt\Component\HunterArgs;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HuntCommand extends Command
{
    const CMD_NAME = 'hunt';

    const CMD_VERSION = '1.1.0';

    private $hunter;

    /**
     * @var string|null The default command name
     */
    protected static $defaultName = self::CMD_NAME;

    /**
     * @var HunterArgs
     */
    private $hunterArgs;

    protected function configure()
    {
        $this->hunterArgs = new HunterArgs($this);
        $this->hunterArgs->configure();
    }

    /**
     * Execute our hunter command.
     *
     * @param InputInterface  $input  the input object
     * @param OutputInterface $output the output object
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->hunter = new Hunter();
        $this->hunterArgs->apply($this->hunter, $input, $output);
        $this->hunter->hunt();
    }

    public function getHunterArgs(): HunterArgs
    {
        return $this->hunterArgs;
    }

    public function getHunter(): Hunter
    {
        return $this->hunter;
    }
}
