<?php

namespace Accela;
require_once __DIR__ . "/functions.php";
require_once __DIR__ . "/scss.inc.php";

class Accela {
  public static function route($path){
    if($path === "/assets/site.json"){
      if(defined("SERVER_LOAD_INTERVAL")){
        header("Cache-Control: max-age=" . SERVER_LOAD_INTERVAL);
      }
      header("Content-Type: application/json");
      $pages = array_map(function($page){
        return [
          "path" => $page->path,
          "head" => $page->head,
          "content" => $page->body,
          "props" => $page->props
        ];
      }, Page::all());
      echo json_encode($pages);
      return;
    }

    if($path === "/assets/js/accela.js"){
      if(defined("SERVER_LOAD_INTERVAL")){
        header("Cache-Control: max-age=" . SERVER_LOAD_INTERVAL);
      }
      header("Content-Type: text/javascript");
      echo file_get_contents(__DIR__ . "/../static/marked.min.js");
      echo file_get_contents(__DIR__ . "/../static/modules.js");
      echo file_get_contents(APP_DIR . "/script.js");
      echo file_get_contents(__DIR__ . "/../static/accela.js");
      return;
    }

    if(preg_match("@/api/(.+)$@", $path, $m)){
      if(API::route($m[1])) return;
    }

    // $path_info = el($_SERVER, "PATH_INFO", "/");
    $path_info = $path;

    try{
      $page = new Page($path_info);
    }catch(PageNotFoundError $e){
      $page = new Page("/404");
      http_response_code(404);
    }
    require __DIR__ . "/../views/template.php";
  }

  public static function api($path, $callback){
    API::register($path, $callback);
  }

  public static function api_paths($dynamic_path, $paths){
    API::register_paths($dynamic_path, $paths);
  }

  public static function global_props($getter){
    PageProps::register_global($getter);
  }

  public static function page_props($path, $getter){
    PageProps::register($path, $getter);
  }

  public static function page_paths($path, $getter){
    PagePaths::register($path, $getter);
  }
}
