<?php
/**
 * template for postqueues tools admin interface
 * @var $this \Postqueue\Editor
 * @var $store \Postqueue\Store
 */
?>
<script type="text/javascript">
	//TODO: hard coded?!? please not!
	window.ph_postqueue_feed_url = "<?php echo get_site_option( 'ph-postqueue-feeds-url', '' ); ?>";
</script>
<div class="wrap ph-postqueue" id="post-queue-editor">
    <h3>Postqueues</h3>
    <div class="loader"><div class="bar"></div></div>
</div>