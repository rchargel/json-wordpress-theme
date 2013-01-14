<?php get_header(); ?>
<?php
if (have_posts()) {
  while(have_posts()) {
    the_post();
    global $id;
    $post = get_post($id);
    if (isset($post)) {
      $pstr = '"post":{"id":%s,"next":%s,"prev":%s,"date":"%s","title":"%s","categories":%s,"comment_status":"%s","ping_status":"%s","comment_count":%s,"comments":%s,"attachments":%s,"content":"%s"},';
      $frmt = get_option('date_format');

      $next = get_next_post();
      $prev = get_previous_post();

      $npost = 'null';
      $ppost = 'null';
      if (isset($next) && $next->ID > 0) {
        $npost = $next->ID;
      }
      if (isset($prev) && $prev->ID > 0) {
        $ppost = $prev->ID;
      }

      $content = fix_quotes(trim(apply_filters("the_content", get_the_content($id))));
      printf($pstr, $id, $npost, $ppost, get_the_time($frmt, $id), $post->post_title, make_category_array($id), $post->comment_status, $post->ping_status, $post->comment_count, make_comment_array($id), make_attachment_array($id), $content);
    } else {
      echo '"post":{"error":404,"message":"Post not found"},';
    }
  }
} else {
  echo '"post":{"error":404,"message":"Post not found"},';
}
?>
<?php get_footer(); ?>
