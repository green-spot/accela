<?php

namespace Accela;

define("SERVER_LOAD_INTERVAL", 60);

// PageProps
Accela::page_props("/", function(){
  return [
    "name" => "Accela",
  ];
});
