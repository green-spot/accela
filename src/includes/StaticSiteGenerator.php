<?php

namespace Accela;
require_once __DIR__ . "/functions.php";
require_once __DIR__ . "/scss.inc.php";

class StaticSiteGenerator {
  private static $time;

  public static function output(){
    $dir = ROOT_DIR . "/out";

    if(is_file($dir)){
      throw new \Exception("ディレクトリが作成できません。");

    }else if(file_exists($dir)){
      self::clear_dir($dir);

    }else{
      mkdir($dir);
    }

    $root = ROOT_DIR;
    if(file_exists("{$root}/assets")){
      shell_exec("cp -r {$root}/assets {$dir}/assets");
    }
    self::$time = time();

    foreach(Page::get_all_template_paths() as $path){
      if(is_dynamic_path($path)){
        foreach(PagePaths::get($path) as $_path){
          $file_path = "{$dir}{$_path}" . (preg_match("@.*/$@", $_path) ? "index.html" : ".html");
          self::get_page($_path, $file_path);
        }
      }else{
        $file_path = "{$dir}{$path}" . (preg_match("@.*/$@", $path) ? "index.html" : ".html");
        self::get_page($path, $file_path);
      }
    }

    foreach(API::get_all_paths() as $path){
      $file_path = "{$dir}/api/{$path}";
      self::get_page("/api/{$path}", $file_path);
    }

    if(!file_exists("{$dir}/assets/js")) mkdir("{$dir}/assets/js", 0755, true);
    self::get_page("/assets/site.json", "{$dir}/assets/site.json");
    self::get_page("/assets/js/accela.js", "{$dir}/assets/js/accela.js");
    file_put_contents("{$dir}/.htaccess", self::htaccess());
  }

  private static function get_page($path, $file_path){
    $dir_path = dirname($file_path);
    if(!is_dir($dir_path)) mkdir($dir_path, 0755, true);

    ob_start();
    Accela::route($path);
    file_put_contents($file_path, ob_get_contents());
    ob_end_clean();
  }

  private static function clear_dir($dir){
    $result = true;

    $iter = new \RecursiveIteratorIterator(
      new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
      \RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach($iter as $file){
      if($file->isDir()) {
        $result &= rmdir($file->getPathname());
      }else{
        $result &= unlink($file->getPathname());
      }
    }
    return $result;
  }

  private static function htaccess(){
    return <<<S
RewriteEngine on
RewriteCond %{THE_REQUEST} ^.*/index.html
RewriteRule ^(.*)index.html$ $1 [R=301,L]

RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME}.html -f
RewriteRule ^(.*)$ $1.html
S;
  }
}
