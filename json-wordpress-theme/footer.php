<?php /* wp_footer();*/ ?> 
<?php
echo '"theme":{"author":"Z Carioca","name":"JSON Theme for Wordpress","version":"0.5"}}';
if ($_REQUEST['callback'] != null) {
   echo ');';
}
global $HTTP_ACCEPT_ENCODING;
if (headers_sent()) {
  $encoding = false;
} elseif (strpos($HTTP_ACCEPT_ENCODING, 'x-gzip') !== false) {
  $encoding = 'x-gzip';
} elseif (strpos($HTTP_ACCEPT_ENCODING,'gzip') !== false) {
  $encoding = 'gzip';
} else {
  $encoding = false;
}

if ($encoding) {
  $contents = ob_get_contents();
  ob_end_clean();
  header('Content-Encoding: '. $encoding);
  print("\x1f\x8b\x08\x00\x00\x00\x00\x00");
  $size = strlen($contents);
  $contents = gzcompress($contents, 9);
  $contents = substr($contents, 0, $size);
  print($contents);
  exit();
} else {
  ob_end_flush();
  exit();
}
?>
