<?php

namespace Accela;

class NoPagePathsError extends \Exception {}

function page_paths($path, $getter){
  PagePaths::register($path, $getter);
}

class PagePaths {
  public static $getters = [];
  public static $memo = [];

  public static function get($path){
    if(!isset($memo[$path])){
      if(!el(self::$getters, $path)) throw new NoPagePathsError($path);
      $memo[$path] = call_user_func(self::$getters[$path]);
    }

    return $memo[$path];
  }

  public static function register($path, $getter){
    self::$getters[$path] = $getter;
  }
}
