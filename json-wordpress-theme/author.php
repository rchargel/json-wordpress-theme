<?php get_header() ?>
"archives":{"type":"author","title":<?php
if (have_posts()) {
  the_post();
  echo '"'.esc_attr(get_the_author()) . '",';?>
"author":{"name":"<?=get_the_author()?>","email":"<?=get_the_author_meta('user_email')?>","description":"<?=get_the_author_meta('description')?>"},<?php
  rewind_posts();
} else {
  echo 'null';
}
?>"posts":<?=make_post_array(true)?>},
<?php get_footer() ?>

