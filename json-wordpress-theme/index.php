<?php get_header(); ?>
<?php get_sidebar(); ?>
<?php
printf('"pages":%s,', make_page_array());
?>"posts":<?=make_post_array(true)?>,
<?php get_footer(); ?>
