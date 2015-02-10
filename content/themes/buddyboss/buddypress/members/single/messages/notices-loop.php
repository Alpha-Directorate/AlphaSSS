<?php do_action( 'bp_before_notices_loop' ); ?>

<?php if ( bp_has_message_threads() ) : ?>

	<div class="pagination no-ajax" id="user-pag">

		<div class="pag-count" id="messages-dir-count">
			<?php bp_messages_pagination_count(); ?>
		</div>

		<div class="pagination-links" id="messages-dir-pag">
			<?php bp_messages_pagination(); ?>
		</div>

	</div><!-- .pagination -->

	<?php do_action( 'bp_after_notices_pagination' ); ?>
	<?php do_action( 'bp_before_notices' ); ?>
	
    <div id="message-threads" class="messages-notices notices">
    	<?php while ( bp_message_threads() ) : bp_message_thread(); ?>
    	<ul id="notice-<?php bp_message_notice_id(); ?>" class="<?php bp_message_css_class(); ?>">
        		<li class="" style="width:1%;">
				</li>
				<li class="notice-info">
					<strong><?php bp_message_notice_subject(); ?></strong>
					<?php bp_message_notice_text(); ?>
                
                </li>
	
                <li class="notice-activity">
                    <?php if ( bp_messages_is_active_notice() ) : ?>
						<strong><?php bp_messages_is_active_notice(); ?></strong>
					<?php endif; ?>
					<span class="activity"><?php _e( 'Sent:', 'buddyboss' ); ?> <?php bp_message_notice_post_date(); ?></span>
                </li>
				
                <?php do_action( 'bp_notices_list_item' ); ?>

				<li class="thread-options">
					<a class="button" href="<?php bp_message_activate_deactivate_link(); ?>" class="confirm"><?php bp_message_activate_deactivate_text(); ?></a>
					<a class="button delete" href="<?php bp_message_notice_delete_link(); ?>" class="confirm" title="<?php _e( "Delete Message", "buddyboss" ); ?>"><?php _e( "Delete", "buddyboss" ); ?></a>
				</li>
        </ul>
        <?php endwhile; ?>
    </div>

	<?php do_action( 'bp_after_notices' ); ?>

<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( 'Sorry, no notices were found.', 'buddyboss' ); ?></p>
	</div>

<?php endif;?>

<?php do_action( 'bp_after_notices_loop' ); ?>