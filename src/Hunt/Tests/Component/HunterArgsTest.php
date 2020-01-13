<?php

namespace Hunt\Tests\Component;

use Hunt\Bundle\Command\HuntCommand;
use Hunt\Bundle\Templates\FileListTemplate;
use Hunt\Bundle\Templates\TemplateFactory;
use Hunt\Component\Hunter;
use Hunt\Component\HunterArgs;
use Hunt\Tests\HuntTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Most of this class is covered by HuntCommandTest; these tests fill in the gaps.
 *
 * @codeCoverageIgnore
 * @coversDefaultClass \Hunt\Component\HunterArgs
 *
 * @uses \Hunt\Component\HunterArgs
 * @uses \Hunt\Bundle\Command\HuntCommand
 * @uses \Hunt\Component\Gatherer\GathererFactory
 * @uses \Hunt\Component\Hunter
 * @uses \Hunt\Component\HunterFileListTraversable
 * @uses \Hunt\Component\Gatherer\AbstractGatherer
 * @uses \Hunt\Bundle\Templates\TemplateFactory
 * @uses \Hunt\Component\OutputStyler
 *
 * @internal
 */
class HunterArgsTest extends HuntTestCase
{
    /**
     * @var HunterArgs
     */
    private $hunterArgs;

    /**
     * @var Hunter
     */
    private $hunter;

    /**
     * @var HuntCommand
     */
    private $huntCommand;

    private $tester;

    public function setup()
    {
        $application = new Application(HuntCommand::CMD_NAME, HuntCommand::CMD_VERSION);
        $this->huntCommand = $application->add(new HuntCommand());

        $this->tester = new CommandTester($this->huntCommand);
    }

    /**
     * @covers ::get
     */
    public function testGetWithNonExistentArgIsNull()
    {
        $this->tester->execute([HunterArgs::TERM => self::SEARCH_TERM]);
        $this->assertNull($this->huntCommand->getHunterArgs()->get('bad argument'));
    }

    /**
     * @covers ::getTemplate
     */
    public function testSetTemplateWithListOnlyOption()
    {
        $this->tester->execute(
            [
                HunterArgs::TERM => self::SEARCH_TERM,
                '--' . HunterArgs::LIST_ONLY => true,
            ]
        );

        $this->assertInstanceOf(FileListTemplate::class, $this->huntCommand->getHunter()->getTemplate());
    }
}
