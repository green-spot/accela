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

    $this->props = PageProps::get($path);
  }

  public static function instance(){
    static $instance;
    if(!$instance) $instance = new self();
    return $instance;
  }
}
