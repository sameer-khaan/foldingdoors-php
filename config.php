<?php

date_default_timezone_set('America/Chicago');
set_time_limit(0);

function isSecure() {
    return
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || $_SERVER['SERVER_PORT'] == 443;
}

define("DB_HOST", "localhost");
define("DB_NAME", "foldingdoors");
define("DB_USER", 'root');
define("DB_PASS", '123456');

define('STDIN',fopen("php://stdin","r"));
define('MAIN_DIR',  '/ewds/foldingdoors/php');

ORM::configure('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME);
ORM::configure('username', DB_USER);
ORM::configure('password', DB_PASS);
ORM::configure('return_result_sets', true);
ORM::configure('driver_options', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));

?>