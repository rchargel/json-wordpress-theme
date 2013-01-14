<?php
header('Content-type: application/json; charset=UTF-8', true);
#header('Content-type: application/json; charset=ISO-8859-1', true);

# GZIP Page Contents
ob_start("ob_gzhandler");
ob_implicit_flush(0);
$date_format = get_option('date_format');
if ($_REQUEST['callback'] != null) {
   echo $_REQUEST['callback'] . '(';
}
?>
{"header":{"cms_name":"Wordpress","title":"<?=get_bloginfo('name')?>",
"tagline":"<?=get_bloginfo('description')?>",
"version":"<?=get_bloginfo('version')?>",
"charset":"<?=get_bloginfo('charset')?>",
"forms":{"admin":{"url":"<?=get_bloginfo('url')?>/wp-login.php","method":"get","parameters":{}},"comment":{"url":"<?=get_bloginfo('url')?>/wp-comments-post.php","method":"post","parameters":{"comment_post_ID":"ID of the parent comment, or null","comment_parent":"ID of the post/page"}},
"search":{"url":"<?=get_bloginfo('url')?>/","method":"get","parameters":{"s":"The search term"}}}},
