<?php
function make_link($name, $url, $props = null) {
  $name = trim($name);
  $str = sprintf('"name":"%s","url":"%s"', $name, $url);
  if (isset($props) && is_array($props)) {
    foreach ($props as $key => $val) {
      $str .= sprintf(',"%s":"%s"',$key,$val);
    }
  }
  return '{'.$str.'}';
}

function fix_quotes($str = '') {
  $str = str_replace('"', '\"', $str);
  $str = str_replace("\n", '\\n', $str);
  return str_replace("\r", ' ', $str);
}

function make_attachment_array($post_id = 0) {
  $output = '';
  $attachments = get_posts("post_type=attachment&post_parent=$post_id");

  if (isset($attachments)) {
    $astr = '{"id":%s,"post_id":%s,"mime_type":"%s","url":"%s","width":%s,"height":%s,"title":"%s","caption":"%s","content":"%s"},';
    foreach($attachments as $media) {
      $img = wp_get_attachment_image_src($media->ID);
      $content = fix_quotes(str_replace("\n", '<br />', $media->post_content));
      $img_url = '';
      $img_width = 'null';
      $img_height = 'null';
      if (!empty($img)) {
        $img_url = $img[0];
        $img_width = $img[1];
        $img_height = $img[2];
      }
      $output .= sprintf($astr, $media->ID, $post_id ,$media->post_mime_type, $img_url, $img_width, $img_height, $media->post_title, $media->post_excerpt, $content);
    }
    $output = substr($output, 0, -1);
  }
  return '[' . $output . ']';
}

function make_comment_array($post_id = 0, $parent = 0) {
  $output = '';
  $comments = get_comments("post_id=$post_id&status=approve&parent=$parent");

  if (isset($comments)) {
    $cmstr = '{"id":%s,"post_id":%s,"author":{"name":"%s","email":"%s","url":"%s"},"date":"%s","karma":%s,"content":"%s","children":%s},';
    foreach($comments as $comment) {
      $cid = $comment->comment_ID;
      $pid = $comment->comment_post_ID;
      $author = $comment->comment_author;
      $email = $comment->comment_author_email;
      $url = $comment->comment_author_url;
      $frmt = get_option('date_format') . ' ' . get_option('time_format');
      $date = get_comment_date($frmt, $cid);
      $karma = $comment->comment_karma;
      $content = fix_quotes(trim(apply_filters('the_content', $comment->comment_content)));

      $output .= sprintf($cmstr, $cid, $pid, $author, $email, $url, $date, $karma, $content, make_comment_array($post_id, $cid));
    }
  }
  return '['.substr($output,0,-1).']';
}

function make_category_array($post_id = 0) {
  $output = '';
  
  foreach((get_the_category($post_id)) as $category) {
    $output = '{"id":'.$category->cat_ID.',"name":"'.$category->cat_name.'","slug":"'.$category->slug.'","description":"'.$category->category_description.'"},';
  }
  return '['.substr($output,0,-1).']';
}

function make_page_array($parent_id = 0) {
  $output = '';

  $pages = get_pages("sort_column=menu_order,post_date&child_of=$parent_id");
  if (isset($pages)) {
    $pstr = '{"date":"%s","title":"%s","url":"%s","id":%s,"slug":"%s","guid":"%s","menu_order":%s,"attachments":%s,"children":%s},';
    $frmt = get_option('date_format');
    foreach ($pages as $page) {
      $pid = $page->ID;
      $date = get_the_time($frmt, $pid);
      $title = get_the_title($pid);
      $url = get_permalink($pid);
      $menu_order = $page->menu_order;
      $attachments = make_attachment_array($pid);
      $children = make_page_array($pid);

      $output .= sprintf($pstr, $date, $title, $url, $pid, $page->post_name, $page->guid, $menu_order, $attachments, $children);
    }
    $output = substr($output, 0, -1);
  }
  return '['.$output.']';
}

function get_json_author() {
  $astr = '"author":{"name":"%s","email":"%s","description":"%s","url":"%s"}';
  return sprintf($astr, get_the_author(), get_the_author_meta('user_email'), get_the_author_meta('description'), get_author_posts_url(get_the_author()));
}

function make_post_array($excerpt = false) {
  $output = '';
  if (have_posts()) {
    $frmt = get_option('date_format');
    $pstr = '{"author":{"name":"%s","url":"%s","email":"%s"},"date":"%s","gmt_date":"%s","title":"%s","categories":%s,"type":"%s","id":%s,"slug":"%s","guid":"%s","url":"%s","attachments":%s,"content":"%s"},';
    while (have_posts()) {
      the_post();
      global $authordata;
  
      $pid = get_the_ID();
      $post = get_post($pid);
      $author = get_the_author();
      $author_url = get_author_posts_url($authordata->ID, $authordata->user_nicename);
      $author_email = get_the_author_meta('user_email');
      $content = trim(apply_filters('the_content',get_the_content()));
      if ($excerpt) {
        $content = trim(apply_filters('the_content', get_the_excerpt()));
      }
      $content = fix_quotes($content);
      $pid = get_the_ID();
      $title = get_the_title($pid);
      $categories = make_category_array($pid);
      $url = get_permalink($pid);
      $dateStr = get_the_time($frmt, $pid);
      $timestamp = strtotime(get_the_time('D, d M Y H:i:s O', $pid));
  
      $output .= sprintf($pstr, $author, $author_url, $author_email, $dateStr, gmdate('D, d M Y H:i:s O', $timestamp), $title, $categories, get_post_type($pid), get_the_ID(), $post->post_name, $post->guid, $url,make_attachment_array($pid), $content);
    }
    $output = substr($output, 0, -1);
  };

  return '[' . $output . ']';
}

function json_get_bookmarks() {
  $cats = get_categories('type=link');

  $catout = '[';

  foreach ($cats as $cat) {
    $cstr = '{"name":"%s","links":%s},';
    $cname = $cat->cat_name;
    $cid = $cat->term_id;
    $links = get_bookmarks("category=$cid");

    $output = '[';

    foreach ($links as $link) {
      $url = $link->link_url;
      $name = $link->link_name;

      $output .= make_link($name, $url) . ',';
    }
    $output = substr($output, 0, -1) . ']';

    $catout .= sprintf($cstr, $cname, $output);
  }
  $catout = substr($catout, 0, -1) . ']';

  return $catout;
}

function json_get_categories() {
  $cats = get_categories();

  $output = '[';

  foreach ($cats as $cat) {
    $clink = get_category_link($cat->term_id);
    $cname = $cat->cat_name;
    $cid = $cat->term_id;
    $slug = $cat->slug;
    $output .= sprintf('{"url":"%s","name":"%s","id":%s,"slug":"%s"},', $clink, $cname, $cid,$slug);
  }
  $output = substr($output, 0, -1).']';

  return $output;
}

function json_get_archives($args = '') {
  global $wpdb, $wp_locale;

  $defaults = array(
    'type' => 'monthly', 'limit' => '',
    'format' => 'html', 'before' => '',
    'after' => '', 'show_post_count' => false,
    'echo' => 0
  );

  $r = wp_parse_args( $args, $defaults );
  extract( $r, EXTR_SKIP );

  if ( '' == $type )
    $type = 'monthly';

  if ( '' != $limit ) {
    $limit = absint($limit);
    $limit = ' LIMIT '.$limit;
  }

  // this is what will separate dates on weekly archive links
  $archive_week_separator = '&#8211;';

  // over-ride general date format ? 0 = no: use the date format set in Options, 1 = yes: over-ride
  $archive_date_format_over_ride = 0;

  // options for daily archive (only if you over-ride the general date format)
  $archive_day_date_format = 'Ymd';

  // options for weekly archive (only if you over-ride the general date format)
  $archive_week_start_date_format = 'Ymd';
  $archive_week_end_date_format   = 'Ymd';

  if ( !$archive_date_format_over_ride ) {
    $archive_day_date_format = get_option('date_format');
    $archive_week_start_date_format = get_option('date_format');
    $archive_week_end_date_format = get_option('date_format');
  }

  //filters
  $where = apply_filters('getarchives_where', "WHERE post_type = 'post' AND post_status = 'publish'", $r );
  $join = apply_filters('getarchives_join', "", $r);

  $output = '';

  if ( 'monthly' == $type ) {
    $query = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY post_date DESC $limit";
    $key = md5($query);
    $cache = wp_cache_get( 'wp_get_archives' , 'general');
    if ( !isset( $cache[ $key ] ) ) {
      $arcresults = $wpdb->get_results($query);
      $cache[ $key ] = $arcresults;
      wp_cache_set( 'wp_get_archives', $cache, 'general' );
    } else {
      $arcresults = $cache[ $key ];
    }
    if ( $arcresults ) {
      $afterafter = $after;
      foreach ( (array) $arcresults as $arcresult ) {
        $url = get_month_link( $arcresult->year, $arcresult->month );
        /* translators: 1: month name, 2: 4-digit year */
        $text = sprintf(__('%1$s %2$d'), $wp_locale->get_month($arcresult->month), $arcresult->year);
        if ( $show_post_count )
          $after = ' ('.$arcresult->posts.')' . $afterafter;
        $output .= make_link("$before $text $after", $url, array('month' => $arcresult->month, 'year' => $arcresult->year)) . ",";
      }
    }
  } elseif ('yearly' == $type) {
    $query = "SELECT YEAR(post_date) AS `year`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(post_date) ORDER BY post_date DESC $limit";
    $key = md5($query);
    $cache = wp_cache_get( 'wp_get_archives' , 'general');
    if ( !isset( $cache[ $key ] ) ) {
      $arcresults = $wpdb->get_results($query);
      $cache[ $key ] = $arcresults;
      wp_cache_set( 'wp_get_archives', $cache, 'general' );
    } else {
      $arcresults = $cache[ $key ];
    }
    if ($arcresults) {
      $afterafter = $after;
      foreach ( (array) $arcresults as $arcresult) {
        $url = get_year_link($arcresult->year);
        $text = sprintf('%d', $arcresult->year);
        if ($show_post_count)
          $after = ' ('.$arcresult->posts.')' . $afterafter;
        $output .= make_link("$before $text $after", $url, array('year' => $arcresult->year)) . ",";
      }
    }
  } elseif ( 'daily' == $type ) {
    $query = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, DAYOFMONTH(post_date) AS `dayofmonth`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(post_date), MONTH(post_date), DAYOFMONTH(post_date) ORDER BY post_date DESC $limit";
    $key = md5($query);
    $cache = wp_cache_get( 'wp_get_archives' , 'general');
    if ( !isset( $cache[ $key ] ) ) {
      $arcresults = $wpdb->get_results($query);
      $cache[ $key ] = $arcresults;
      wp_cache_set( 'wp_get_archives', $cache, 'general' );
    } else {
      $arcresults = $cache[ $key ];
    }
    if ( $arcresults ) {
      $afterafter = $after;
      foreach ( (array) $arcresults as $arcresult ) {
        $url    = get_day_link($arcresult->year, $arcresult->month, $arcresult->dayofmonth);
        $date = sprintf('%1$d-%2$02d-%3$02d 00:00:00', $arcresult->year, $arcresult->month, $arcresult->dayofmonth);
        $text = mysql2date($archive_day_date_format, $date);
        if ($show_post_count)
          $after = ' ('.$arcresult->posts.')'.$afterafter;
        $output .= make_link("$before $text $after", $url, array('month' => $arcresult->month, 'year' => $arcresult->year, 'day' => $arcresult->dayofmonth)) . ",";
      }
    }
  } elseif ( 'weekly' == $type ) {
    $week = _wp_mysql_week( '`post_date`' );
    $query = "SELECT DISTINCT $week AS `week`, YEAR( `post_date` ) AS `yr`, DATE_FORMAT( `post_date`, '%Y-%m-%d' ) AS `yyyymmdd`, count( `ID` ) AS `posts` FROM `$wpdb->posts` $join $where GROUP BY $week, YEAR( `post_date` ) ORDER BY `post_date` DESC $limit";
    $key = md5($query);
    $cache = wp_cache_get( 'wp_get_archives' , 'general');
    if ( !isset( $cache[ $key ] ) ) {
      $arcresults = $wpdb->get_results($query);
      $cache[ $key ] = $arcresults;
      wp_cache_set( 'wp_get_archives', $cache, 'general' );
    } else {
      $arcresults = $cache[ $key ];
    }
    $arc_w_last = '';
    $afterafter = $after;
    if ( $arcresults ) {
        foreach ( (array) $arcresults as $arcresult ) {
          if ( $arcresult->week != $arc_w_last ) {
            $arc_year = $arcresult->yr;
            $arc_w_last = $arcresult->week;
            $arc_week = get_weekstartend($arcresult->yyyymmdd, get_option('start_of_week'));
            $arc_week_start = date_i18n($archive_week_start_date_format, $arc_week['start']);
            $arc_week_end = date_i18n($archive_week_end_date_format, $arc_week['end']);
            $url  = sprintf('%1$s/%2$s%3$sm%4$s%5$s%6$sw%7$s%8$d', home_url(), '', '?', '=', $arc_year, '&amp;', '=', $arcresult->week);
            $text = $arc_week_start . $archive_week_separator . $arc_week_end;
            if ($show_post_count)
              $after = ' ('.$arcresult->posts.')'.$afterafter;
            $output .= make_link("$before $text $after", $url) . ",";
          }
        }
    }
  } elseif ( ( 'postbypost' == $type ) || ('alpha' == $type) ) {
    $orderby = ('alpha' == $type) ? "post_title ASC " : "post_date DESC ";
    $query = "SELECT * FROM $wpdb->posts $join $where ORDER BY $orderby $limit";
    $key = md5($query);
    $cache = wp_cache_get( 'wp_get_archives' , 'general');
    if ( !isset( $cache[ $key ] ) ) {
      $arcresults = $wpdb->get_results($query);
      $cache[ $key ] = $arcresults;
      wp_cache_set( 'wp_get_archives', $cache, 'general' );
    } else {
      $arcresults = $cache[ $key ];
    }
    if ( $arcresults ) {
      foreach ( (array) $arcresults as $arcresult ) {
        if ( $arcresult->post_date != '0000-00-00 00:00:00' ) {
          $url  = get_permalink($arcresult);
          $arc_title = $arcresult->post_title;
          if ( $arc_title )
            $text = strip_tags(apply_filters('the_title', $arc_title));
          else
            $text = $arcresult->ID;
          $output .= make_link("$before $text $after", $url) . ",";
        }
      }
    }
  }
  $output = '['.substr($output, 0, -1).']';
  if ( $echo ) {
    echo $output;
  } else {
    return $output;
  }
}

if ( function_exists('register_sidebar') )
	register_sidebar(array(
        'before_widget' => '<li id="%1$s" class="widget %2$s">',
        'after_widget' => '</li>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ));

?>
