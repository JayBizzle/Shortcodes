<?php

$dot = dirname(__FILE__);

if (! file_exists($composer = dirname($dot).'/vendor/autoload.php')) {
    throw new RuntimeException("Please run 'composer install' first to set up autoloading. $composer");
}
/** @var \Composer\Autoload\ClassLoader $autoloader */
$autoloader = include $composer;
