#!/usr/bin/env php
<?php

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Console\Application;

//
$console = new Application();
$console->add(new CreatePharCommand());
$console->add(new ListPharContentCommand());
$console->run();
