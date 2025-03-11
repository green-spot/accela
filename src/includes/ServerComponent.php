<?php

namespace Accela;

class ServerComponentNotFoundError extends \Exception {}

class ServerComponent {
  public string $path;

  public function __construct(){
  }

  public static function load(string $component_name): ServerComponent {
    $sc = new ServerComponent();
    $sc->path = APP_DIR . "/server-components/{$component_name}.php";

    if(!is_file($sc->path)){
      throw new ServerComponentNotFoundError("'{$component_name}' server component not founds.");
    }

    return $sc;
  }

  public function evaluate(array $props, string $content): string {
    $sc = $this;

    return capture(function()use($sc, $props, $content): void {
      include $sc->path;
    });
  }
}
