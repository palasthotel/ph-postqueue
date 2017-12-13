<?php
/**
 * template for postqueues metabox
 * @var $this \Postqueue\Metabox
 * @var $store \Postqueue\Store
 */
?>
<div class="postqueue-metabox-wrapper">
	<p class="description">
  	<?php esc_html_e( 'Lets you add this post to a postqueue or delete it from.', Postqueue\Plugin::DOMAIN ); ?>
  </p>
  <p class="messages"></p>
  <div class="postqueue-metabox-postqueuelist-wrapper">
    <?php
      $postqueues = $store->get_queues_for_post( get_the_ID() );
    ?>
    <h4><?php esc_html_e( 'Linked postqueues', Postqueue\Plugin::DOMAIN ); ?></h4>
    <ul>
      <?php foreach ( $postqueues as $postqueue ): ?>
      <?php $postqueue = $postqueue[0]; ?>
        <li><?php echo $postqueue->name; ?> <span class="dashicons dashicons-no postqueue-remove" data-queueid="<?php echo $postqueue->queue_id; ?>" data-postid="<?php echo get_the_ID(); ?>" title="<?php esc_html_e( 'Remove post from this postqueue.', Postqueue\Plugin::DOMAIN ); ?>" data-queuename="<?php echo $postqueue->name; ?>"></span></li>
      <?php endforeach; ?>
    </ul>
  </div>
  
  <hr />
  <div class="postqueue-metabox-postqueueselect-wrapper">
    <?php
      $all_postqueues = $store->get_queues();
    ?>
    <h4><?php esc_html_e( 'Add post to postqueue', Postqueue\Plugin::DOMAIN ); ?></h4>
    <?php if ( count( $all_postqueues ) > 0 ): ?>
      <select name="postqueue-select" class="postqueue-select">
        <option value="none">-- <?php esc_html_e( 'choose', Postqueue\Plugin::DOMAIN ); ?> --</option>
      <?php foreach ( $all_postqueues as $postqueue ): ?>
        <?php if( !$store->is_post_in_queue( get_the_ID(), $postqueue->id) ): ?>
          <option value="<?php echo $postqueue->id; ?>" data-queuename="<?php echo $postqueue->name; ?>"><?php echo $postqueue->name; ?></option>
        <?php endif; ?>
      <?php endforeach; ?>
      </select>
    <?php else: ?>
      <?php esc_html_e( 'There are no available postqueues yet.', Postqueue\Plugin::DOMAIN ); ?> (@todo link zur settings page)
    <?php endif; ?>
    <span class="button button-secondary hide-if-no-js postqueue-add" data-postid="<?php echo get_the_ID(); ?>"><?php esc_html_e( 'Add', Postqueue\Plugin::DOMAIN ); ?></span>
  </div>
</div>