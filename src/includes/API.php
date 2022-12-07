<?php

namespace Accela;

class API {
  public static $map = [];
  public static $paths_list = [];

  public static function route($path){
    foreach(self::$map as $_path => $callback){
      if($path === $_path){
        self::response_header($path);
        $callback();
        return true;
      }

      $path_regexp = preg_replace('/(\[.+?\])/', '(.+?)', $_path);
      if(preg_match("@{$path_regexp}@", $path, $matches)){
        if(in_array($path, self::$paths_list[$_path]())){
          self::response_header($path);
          $callback(API::build_query($_path, $matches));
          return true;
        }
      }
    }

    return false;
  }

  public static function build_query($path, $matches){
    $query = [];

    preg_match_all('@\[([a-z]+)\]@', $path, $m);
    foreach($m[1] as $i => $key){
      $query[$key] = $matches[$i+1];
    }

    return $query;
  }

  public static function get_all_paths(){
    $paths = [];

    foreach(self::$map as $path => $_){
      if(strpos($path, "[") === FALSE) $paths[] = $path;
    }

    foreach(self::$paths_list as $_ => $get_paths){
      $paths = array_merge($paths, $get_paths());
    }

    return $paths;
  }

  public static function response_header($path){
    preg_match('/\.(.*?)$/', $path, $m);
    if(!$m) return;

    $mimes = [
      "json" => "application/json",
      "csv" => "text/csv",
      "html" => "text/html"
    ];

    $mime = el($mimes, $m[1], "text/plain");
    header("Content-Type: {$mime}");
  }

  public static function register($path, $callback){
    if(!preg_match('@^[/.a-z0-9\-_\[\]]+$@', $path)) return;
    self::$map[$path] = $callback;
  }

  public static function register_paths($dynamic_path, $get_paths){
    self::$paths_list[$dynamic_path] = function()use($get_paths){
      static $memo;
      if(!$memo) $memo = $get_paths();
      return $memo;
    };
  }
}
