<?php

namespace Accela;

class ComponentNotFoundError extends \Exception {}

class Component {
  public string $content;

  public function __construct(string $component_name){
    $abs_file_path = APP_DIR . "/components/{$component_name}.html";

    if(!is_file($abs_file_path)){
      throw new ComponentNotFoundError("'{$abs_file_path}' component not founds.");
    }

    $content = file_get_contents($abs_file_path);
    $content = preg_replace("/^[\\s\\t]+/mu", "", $content);
    $this->content = preg_replace("/\\n+/mu", "\n", $content);
  }

  /**
   * @return Component[]
   */
  public static function all(): array {
    $walk = function(string $dir, array &$components=[])use(&$walk): array {
      foreach(scandir($dir) as $file){
        if(in_array($file, [".", ".."])) continue;

        $file_path = $dir . $file;
        if(is_dir($file_path)){
          $walk("{$file_path}/", $components);

        }else if(is_file($file_path) && preg_match("@.*\\.html$@", $file)){
          $path = str_replace(".html", "", $file_path);
          $path = str_replace(APP_DIR . '/components/', "", $path);
          $components[$path] = new Component($path);
        }
      }

      return $components;
    };

    return $walk(APP_DIR ."/components/");
  }
}
