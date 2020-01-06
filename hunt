#!/usr/bin/env php

<?php

require_once __DIR__ . '/vendor/autoload.php';

use Hunt\Bundle\Command\HuntCommand;
use Symfony\Component\Console\Application;

$command = new HuntCommand();

$application = new Application(HuntCommand::CMD_NAME, HuntCommand::CMD_VERSION);
$application->add($command);
$application->setDefaultCommand(HuntCommand::CMD_NAME, true)
    ->run();
