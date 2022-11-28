<?php

namespace Accela {
  function el($object, $key, $default=null){
    if(is_array($object)){
      return isset($object[$key]) ? $object[$key] : $default;
    }else{
      return isset($object->$key) ? $object->$key : $default;
    }
  }

  function get_utime(){
    $now = time();
    if(defined("SERVER_LOAD_INTERVAL")) $now = $now - ($now % SERVER_LOAD_INTERVAL);

    return el($_GET, "__t", $now);
  }

  function get_initial_data($page){
    return [
      "entrance_page" => [
        "path" => $page->path,
        "head" => $page->head,
        "content" => $page->body,
        "props" => $page->props
      ],
      "components" => get_components(),
      "utime" => get_utime()
    ];
  }

  function get_components(){
    $components = [];
    foreach(Component::all() as $name => $component){
      $components[$name] = $component->content;
    }
    return $components;
  }

  function get_header_html($page){
    $common_page = PageCommon::instance();
    $separator = "\n<meta name=\"accela-separator\">\n";
    return $common_page->head . $separator . $page->head;
  }

  function is_dynamic_path($path){
    return preg_match("@\\[.+?\\]@", $path);
  }

  function capture($callback){
    ob_start();
    $callback();
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
  }
}
