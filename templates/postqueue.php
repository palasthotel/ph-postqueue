<?php
/**
 * @var $query_args array WP_Query arguments
 * @var $viewmode string optional viewmode attribute on shortcode
 */
echo '<ul class="postqueue__list">';
$query = new WP_Query( $query_args );
while ( $query->have_posts() ) {
	$query->the_post();
	$permalink  = get_the_permalink();
	$title = get_the_title();
	echo "<li><a class='{$viewmode}' href='{$permalink}'>{$title}</a></li>";
}
echo '</ul>';