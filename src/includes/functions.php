<?php

namespace Accela {
  /**
   * @param array | object $object
   * @param string $key
   * @param mixed $default
   * @return mixed
   */
  function el(mixed $object, string $key, mixed $default=null): mixed {
    if(is_array($object)){
      return isset($object[$key]) ? $object[$key] : $default;
    }else{
      return isset($object->$key) ? $object->$key : $default;
    }
  }

  function getUtime(): string {
    $now = time();
    if(defined("SERVER_LOAD_INTERVAL")) $now = $now - ($now % constant("SERVER_LOAD_INTERVAL"));

    return el($_GET, "__t", "{$now}");
  }

  function getInitialData(Page $page): array {
    return [
      "entrancePage" => [
        "path" => $page->path,
        "head" => $page->head,
        "content" => $page->body,
        "props" => $page->props
      ],
      "globalProps" => PageProps::$global_props,
      "components" => getComponents(),
      "utime" => getUtime()
    ];
  }

  /**
   * @return Component[]
   */
  function getComponents(): array {
    $components = [];
    foreach(Component::all() as $name => $component){
      $components[$name] = $component->content;
    }
    return $components;
  }

  function getHeaderHtml(Page $page): string{
    $common_page = PageCommon::instance();
    $separator = "\n<meta name=\"accela-separator\">\n";
    return $common_page->head . $separator . $page->head;
  }

  function isDynamicPath(string $path): bool {
    return !!preg_match("@\\[.+?\\]@", $path);
  }

  function capture(callable $callback): string {
    ob_start();
    $callback();
    $output = ob_get_contents();
    ob_end_clean();
    return $output ?: "";
  }
}
