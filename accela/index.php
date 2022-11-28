<?php

define("ROOT_DIR", __DIR__);
define("APP_DIR", ROOT_DIR . "/app");

require_once __DIR__ . "/vendor/autoload.php";
Accela\Accela::route(isset($_SERVER["PATH_INFO"]) ? $_SERVER["PATH_INFO"] : "/");
