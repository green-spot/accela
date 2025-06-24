<?php
use function Accela\el;

$dom = new DOMDocument('1.0', 'UTF-8');
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->loadXML(Accela\capture(function(){
?>
<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <?php
    $pages = Accela\Page::all();
    usort($pages, function($x, $y) {
      return strcmp($x->path, $y->path);
    });

    $base = constant("ROOT_URL");
    if(!$base){
      $base = (el($_SERVER, "HTTPS") === "on" ? "https://" : "http://") . el($_SERVER, "HTTP_HOST");
    }
  ?>
  <?php foreach($pages as $page): ?>
  <?php if($page->path === "/404") continue; ?>
  <?php
    $vars = ["loc" => $base . ($page->static_path ?: $page->path)];

    if($page->meta_dom->getElementById("accela-sitemap-lastmod")?->textContent){
      $vars["lastmod"] = $page->meta_dom->getElementById("accela-sitemap-lastmod")->textContent;
    }else{
      $vars["lastmod"] = date('Y-m-d', filemtime(APP_DIR . "/pages" . $page->path));
    }

    if($page->meta_dom->getElementById("accela-sitemap-changefreq")?->textContent){
      $vars["changefreq"] = $page->meta_dom->getElementById("accela-sitemap-changefreq")->textContent;
    }

    if($page->meta_dom->getElementById("accela-sitemap-priority")?->textContent){
      $vars["priority"] = $page->meta_dom->getElementById("accela-sitemap-priority")->textContent;
    }
  ?>
  <url>
    <?php
      foreach($vars as $k => $v){
        echo "<{$k}>$v</{$k}>";
      }
    ?>
  </url>
  <?php endforeach; ?>
</urlset>

<?php
}));

echo $dom->saveXML();
