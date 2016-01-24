<?php
require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/tests/testUtils.php';

// TimeZone
date_default_timezone_set('Asia/Tokyo');

// class-loader
use Symfony\Component\ClassLoader\Psr4ClassLoader;
$loader = new Psr4ClassLoader();
$loader->addPrefix('Saki\\', __DIR__.'/Saki');
$loader->register();
