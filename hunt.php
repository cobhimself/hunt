#!/usr/bin/env php

<?php

require_once __DIR__ . '/vendor/autoload.php';

use Hunt\Component\Progress;
use Symfony\Component\Console\Application;

use Hunt\Bundle\Command\HuntCommand;

$command = new HuntCommand();

$application = new Application(HuntCommand::CMD_NAME, HuntCommand::CMD_VERSION);
$application->add($command);
$application->setDefaultCommand(HuntCommand::CMD_NAME, true)
    ->run();
