<?php

require_once __DIR__.'/DRLevConfig.php';

if (!defined('STEP_TIMEOUT')) {
    define('STEP_TIMEOUT', DRLevConfig::get('step-timeout') * 1000);
}

function stepSleep() {
    usleep(STEP_TIMEOUT);
}

function console($msg) {
    echo $msg;
    flush();
    ob_flush();
}

function dump() {
    $args = (func_get_args());
    foreach ($args as $arg) {
        var_dump($arg);
    }
    flush();
    ob_flush();
}