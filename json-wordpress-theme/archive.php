<?php get_header() ?>
"archives":{<?php
is_tag();
if (is_category()) { 
?>"type":"category","title":"<? single_cat_title() ?>",<?php
} else if (is_tag()) {
?>"type":"tag","title":"<? single_tag_title() ?>",<?php
} else if (is_day()) {
?>"type":"tag","archive":"<? the_time('F jS, Y') ?>",<?php
} else if (is_month()) {
?>"type":"tag","archive":"<? the_time('F, Y') ?>",<?php
} else if (is_year()) {
?>"type":"tag","archive":"<? the_time('Y') ?>",<?php
}
?>"posts":<?=make_post_array(true)?>},
<?php get_footer() ?>
