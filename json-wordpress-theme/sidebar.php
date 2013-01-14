<?php
$rss_feed = get_bloginfo('rss2_url');
$wp_url   = get_bloginfo('wpurl');

$content = '"sidebar":{"archives":%s,"categories":%s,"bookmarks":%s},';
$archives_content = json_get_archives();
$categories = json_get_categories();
$links = json_get_bookmarks();

printf($content, $archives_content, $categories, $links);
?>
