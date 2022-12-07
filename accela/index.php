<?php

namespace Accela;

require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/app/page-init.php";

define("ROOT_DIR", __DIR__);
define("APP_DIR", ROOT_DIR . "/app");

if(file_exists(__DIR__ . "/env.php")){
  require_once __DIR__ . "/env.php";
}

Accela::route(isset($_SERVER["PATH_INFO"]) ? $_SERVER["PATH_INFO"] : "/");
