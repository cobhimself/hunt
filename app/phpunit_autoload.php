<?php

// composer
if (!$loader = @include $loaderfile = __DIR__ . '/../vendor/autoload.php') {
    $nl = \PHP_SAPI === 'cli' ? \PHP_EOL : '<br />';
    $sp = \PHP_SAPI === 'cli' ? ' ' : '&nbsp;';

    echo 'Unable to load ' . $loaderfile . $nl . $nl .
        'You must set up the project dependencies by running the following commands on ' . php_uname('n') . ':' . $nl . $nl .
        $sp . $sp . $sp . $sp . 'cd ' . dirname(__DIR__) . '/' . $nl .
        $sp . $sp . $sp . $sp . 'composer install' . $nl . $nl;
    exit(1);
}

unset($loader, $loaderpath);
