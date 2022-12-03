<?php

namespace Accela;

class PageNotFoundError extends \Exception {}

class Page {
  static $scss;

  public $path, $head, $body, $style, $props;

  public function __construct($path){
    if(preg_match("@\\[.+\\]@", $path)){
      $path = "/404";
    }

    $file_path = $path . (substr($path, -1) === "/" ? "index.html" : ".html");
    $abs_file_path = APP_DIR . "/pages{$file_path}";

    if(!is_file($abs_file_path)){
      $abs_file_path = __DIR__ . "/../pages{$file_path}";
    }

    if(!is_file($abs_file_path)){
      $static_path = $path;
      $dynamic_path = Page::get_dynamic_path($static_path);

      if($dynamic_path){
        $file_path = $dynamic_path . (substr($dynamic_path, -1) === "/" ? "index.html" : ".html");
        $abs_file_path = APP_DIR . "/pages{$file_path}";
        $content = file_get_contents($abs_file_path);
        $this->initialize($dynamic_path, $content, $static_path);
        $this->is_dynamic = true;
        return;

      }else{
        throw new PageNotFoundError("'{$path}' template file not founds.");
      }
    }

    $content = file_get_contents($abs_file_path);
    $content = preg_replace("/^[\\s\\t]+/mu", "", $content);
    $content = preg_replace("/\\n+/mu", "\n", $content);
    $this->initialize($path, $content);
    $this->is_dynamic = false;
  }

  public function initialize($path, $content, $static_path=null){
    $this->path = $path;
    $this->head = preg_replace("@^.*<head>[\s\t\n]*(.+?)[\s\t\n]*</head>.*$@s", '$1', $content);
    $this->head = preg_replace("@[ \t]+<@", "<", $this->head);
    $this->body = preg_replace("@^.*<body>[\s\t\n]*(.+?)[\s\t\n]*</body>.*$@s", '$1', $content);

    $this->style = "";
    if(preg_match("@<style>.+?</style>@s", $content)){
      $this->style = preg_replace("@^.*<style>[\s\t\n]*(.+?)[\s\t\n]*</style>.*$@s", '$1', $content);
    }

    // get PageProps
    if($static_path){
      preg_match_all("@\\[(.+?)\\]@", $path, $m_keys);

      $path_re = preg_replace("@(\\[.+?\\])@", "([^/]+?)", $path);
      preg_match("@{$path_re}$@", $static_path, $m_vals);

      $query = [];
      foreach($m_keys[1] as $i => $key){
        $query[$key] = $m_vals[$i+1];
      }
      $this->props = PageProps::get($path, $query);


    }else{
      $this->props = PageProps::get($path);
    }

    // eval ServerComponent
    $this->head = $this->evaluate_server_component($this->head, $this->props);
    $this->body = $this->evaluate_server_component($this->body, $this->props);
  }

  public function evaluate_server_component($html, $page_props){
    preg_match_all('@(<server-component\s+(.+?)>(.*?)</server-component>)@ms', $html, $m);
    foreach($m[1] as $i => $tag){
      $props_string = $m[2][$i];
      preg_match_all('/(@?[a-z0-9\-_]+)="(.+?)"/m', $props_string, $m2);
      $props = [];
      foreach($m2[1] as $j => $key){
        if(strpos($key, "@") === 0){
          $props[substr($key, 1)] = el($page_props, $m2[2][$j]);
        }else{
          $props[$key] = $m2[2][$j];
        }
      }

      $content = $m[3][$i];

      $component = ServerComponent::load($props["use"]);
      $evaluated_component = $component ? $component->evaluate($props, $content) : "";
      $html = str_replace($tag, $evaluated_component, $html);
    }

    return $html;
  }

  public function get_css(){
    $css = "[data-page-path='{$this->path}']{{$this->style}}";
    return trim(Page::$scss->compile($css));
  }

  public static function get_dynamic_path($static_path){
    static $memo;
    if(!$memo) $memo = [];
    if(!isset($memo[$static_path])){
      $memo[$static_path] = null;

      foreach(self::get_all_template_paths() as $path){
        if(strpos($path, "[") !== false){
          $re = preg_replace("@(\\[.+?\\])@s", "[^/]+?", $path);
          if(preg_match("@{$re}@", $path)){
            $static_paths = PagePaths::get($path);
            if(in_array($static_path, $static_paths)){
              $memo[$static_path] = $path;
              return $path;
            }
          }
        }
      }
    }

    return $memo[$static_path];
  }

  public static function all(){
    static $pages;
    if(!$pages){
      $pages = [];

      foreach(self::get_all_template_paths() as $path){
        if(preg_match("@\\[.+\\]@", $path)){
          foreach(PagePaths::get($path) as $_path){
            $pages[$_path] = new Page($_path);
          }

        }else{
          $pages[$path] = new Page($path);
        }

      }
    }

    return $pages;
  }

  public static function get_all_template_paths(){
    static $paths;
    if(!$paths){
      $walk = function($dir, &$paths=[])use(&$walk){
        foreach(scandir($dir) as $file){
          if(in_array($file, [".", ".."])) continue;

          $file_path = $dir . $file;
          if(is_dir($file_path)){
            $walk("{$file_path}/", $paths);

          }else if(is_file($file_path) && preg_match("@.*\\.html$@", $file)){
            $path = ($file === "index.html") ? $dir : str_replace(".html", "", $file_path);
            $path = str_replace(APP_DIR . '/pages', "", $path);
            $paths[] = $path;
          }
        }

        return $paths;
      };

      $paths = $walk(APP_DIR . "/pages/");
    }

    return $paths;
  }
}

Page::$scss = new \scssc();
