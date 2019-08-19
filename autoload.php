<?php

function __autoload($className) {
    $filename = dirname(__FILE__) . '/class/' . $className . '.php';
    require_once $filename;
}