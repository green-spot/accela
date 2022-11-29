<?php

namespace Accela;

// lang
define("HTML_LANG", "ja");

// seconds for Cache-Control
// define("SERVER_LOAD_INTERVAL", 60);

// PageProps
Accela::page_props("/", function(){
  return [
    "name" => "Accela",
  ];
});
