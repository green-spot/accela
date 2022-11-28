<?php

namespace Accela;

class PageCommon extends Page {
  public function __construct(){
    parent::__construct("/../common");
  }

  public function initialize($path, $content, $_=null){
    $this->path = $path;
    $this->head = preg_replace("@^.*<head>[\s\t\n]*(.+?)[\s\t\n]*</head>.*$@s", '$1', $content);
    $this->head = preg_replace("@[ \t]+<@", "<", $this->head);
    $this->body = preg_replace("@^.*<body>[\s\t\n]*(.+?)[\s\t\n]*</body>.*$@s", '$1', $content);

    $this->style = "";
    if(preg_match("@<style>.+?</style>@s", $content)){
      $this->style = preg_replace("@^.*<style>[\s\t\n]*(.+?)[\s\t\n]*</style>.*$@s", '$1', $content);
    }

    $this->props = PageProps::get($path);
  }

  public function get_css(){
    return trim(Page::$scss->compile($this->style));
  }

  public static function instance(){
    static $instance;
    if(!$instance) $instance = new self();
    return $instance;
  }
}
