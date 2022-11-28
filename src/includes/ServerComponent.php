<?php

namespace Accela;

class ServerComponentNotFoundError extends \Exception {}

class ServerComponent {
  public function __construct(){
  }

  public static function load($component_name){
    $sc = new ServerComponent();
    $sc->path = APP_DIR . "/server-components/{$component_name}.php";

    if(!is_file($sc->path)){
      throw new ServerComponentNotFoundError("'{$component_name}' server component not founds.");
    }

    return $sc;
  }

  public function evaluate($props, $content){
    $sc = $this;

    return capture(function()use($sc, $props, $content){
      include $sc->path;
    });
  }
}
