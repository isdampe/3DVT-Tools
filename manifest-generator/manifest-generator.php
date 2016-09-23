#!/usr/bin/php
<?php

require_once 'config.php';

if ( count( $argv ) < 3 ) {
  echo "Usage: manifest-generator tour_name[sparite] /path/to/cdn/tours/tour\n";
  exit;
}

$tour_name = $argv[1];
$directory = $argv[2];
if (! file_exists($directory) ) {
  echo "Directory does not exist.\n";
  exit;
}

$path = realpath( $directory );
$base_url = sprintf(BASE_URL, $tour_name);

$files_to_cache = $preloaded_assets;

$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
foreach($objects as $name => $object){

  if (! file_exists($name) ) continue;
  if ( is_dir($name) ) continue;

  $key = sprintf('tours/%s/', $tour_name);
  $nn = substr( $name, strpos($name, $key) + strlen($key) );


  if ( $nn == "." || $nn == ".." ) continue;

  $files_to_cache[] = $base_url . $nn;

}

$cache_entries = "";
foreach ( $files_to_cache as $file ) {
  $cache_entries .= $file . "\n";
}

$version = time();

$output_buffer = str_replace("%VERSION%", $version, $manifest_template);
$output_buffer = str_replace("%ENTRIES%", $cache_entries, $output_buffer);

$out_uri = $directory . "/appcache.manifest";

$fh = fopen($out_uri, 'w');
if (! $fh ) {
  die("Could not open $out_uri for writing.\n");
}
fwrite($fh, $output_buffer);
fclose($fh);

echo "Created $out_uri\n";
