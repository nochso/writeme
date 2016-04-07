<?php
require_once 'vendor/autoload.php';
use nochso\Omni\PhpCsFixer\PrettyPSR;

$config = PrettyPSR::createIn(['src', 'test']);
$config->getFinder()->notName('DocBlockTest.php');
return $config;
