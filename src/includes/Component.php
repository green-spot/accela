<?php

namespace Accela;

class ComponentNotFoundError extends \Exception {}

class Component {
  public function __construct($component_name){
    $abs_file_path = APP_DIR . "/components/{$component_name}.html";

    if(!is_file($abs_file_path)){
      throw new ComponentNotFoundError("'{$abs_file_path}' component not founds.");
    }

    $this->content = file_get_contents($abs_file_path);
  }

  public static function all() {
    function walk($dir, &$components=[]){
      foreach(scandir($dir) as $file){
        if(in_array($file, [".", ".."])) continue;

        $file_path = $dir . $file;
        if(is_dir($file_path)){
          walk("{$file_path}/", $components);

        }else if(is_file($file_path) && preg_match("@.*\\.html$@", $file)){
          $path = str_replace(".html", "", $file_path);
          $path = str_replace(APP_DIR . '/components/', "", $path);
          $components[$path] = new Component($path);
        }
      }

      return $components;
    }

    return walk(APP_DIR ."/components/");
  }
}
