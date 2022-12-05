<?php

namespace Accela;

class PageProps {
  public static $props = [];
  public static $global_props = null;

  public static function get($path, $query=null){
    if(!el(self::$props, $path)) return [];
    return $query ? call_user_func_array(self::$props[$path], [$query]) : call_user_func(self::$props[$path]);
  }

  public static function register($path, $getter){
    self::$props[$path] = $getter;
  }

  public static function register_global($getter){
    self::$global_props = $getter();
  }
}
