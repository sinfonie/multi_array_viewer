<?php

$class_loader = function ($class) {
  $prefix = 'mavLibs\\';
  $base_dir = __DIR__ . '/../';
  $len = strlen($prefix);

  if (strncmp($prefix, $class, $len) !== 0) {
    return;
  }
  $relative_class = substr($class, $len);

  $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
  if (file_exists($file)) {
    require $file;
  }
};

spl_autoload_register($class_loader);