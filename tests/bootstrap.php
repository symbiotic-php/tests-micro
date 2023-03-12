<?php

$composerLoader = dirname(__DIR__,3).'/vendor/autoload.php';
if(file_exists($composerLoader)) {
    include_once dirname(__DIR__,3).'/vendor/autoload.php';
}