<?php

namespace Accela;

function page_props($path, $getter){
  PageProps::register($path, $getter);
}

class PageProps {
  public static $props = [];

  public static function get($path, $query=null){
    if(!el(self::$props, $path)) return [];
    return $query ? call_user_func_array(self::$props[$path], [$query]) : call_user_func(self::$props[$path]);
  }

  public static function register($path, $getter){
    self::$props[$path] = $getter;
  }
}
