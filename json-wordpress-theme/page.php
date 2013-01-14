<?php get_header(); ?>
<?php
if (is_page() && have_posts()) {
  while(have_posts()) {
    the_post();
    global $id;
    $page = get_page($id);
    if (isset($page)) {
      $pstr = '"page":{"id":%s,"date":"%s","title":"%s","comment_status":"%s","ping_status":"%s","comment_count":%s,"children":%s,"comments":%s,"attachments":%s,"content":"%s"},';
      $frmt = get_option('date_format');

      $content = fix_quotes(trim(apply_filters("the_content", get_the_content($id))));
      printf($pstr, $id, get_the_time($frmt, $id), $page->post_title, $page->comment_status, $page->ping_status, $page->comment_count, make_page_array($id), make_comment_array($id), make_attachment_array($id), $content);
    } else {
      echo '"page":{"error":404,"message":"Page not found"},';
    }
  }
} else {
  echo '"page":{"error":404,"message":"Page not found"},';
}
?>
<?php get_footer(); ?>
