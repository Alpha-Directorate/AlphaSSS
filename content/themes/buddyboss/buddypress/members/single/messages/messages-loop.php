<?php do_action( 'bp_before_member_messages_loop' ); ?>

<?php if ( bp_has_message_threads( bp_ajax_querystring( 'messages' ) ) ) : ?>

	<div class="pagination no-ajax" id="user-pag">

		<div class="pag-count" id="messages-dir-count">
			<?php bp_messages_pagination_count(); ?>
		</div>

		<div class="pagination-links" id="messages-dir-pag">
			<?php bp_messages_pagination(); ?>
		</div>

	</div><!-- .pagination -->

	<?php do_action( 'bp_after_member_messages_pagination' ); ?>

	<?php do_action( 'bp_before_member_messages_threads'   ); ?>
    
	<div id="message-threads" class="messages-notices">
    	<?php while ( bp_message_threads() ) : bp_message_thread(); ?>
 
    	<ul id="m-<?php bp_message_thread_id(); ?>" class="<?php bp_message_css_class(); ?><?php if ( bp_message_thread_has_unread() ) : ?> unread<?php else: ?> read<?php endif; ?>">
        		<li class="thread-count">
					<span class="unread-count"><?php bp_message_thread_unread_count(); ?></span>
				</li>
				<li class="thread-avatar">
					<?php bp_message_thread_avatar(); ?> 
                </li>

				<?php if ( 'sentbox' != bp_current_action() ) : ?>
					<li class="thread-from">
						<?php _e( 'From:', 'buddyboss' ); ?> <?php bp_message_thread_from(); ?><br />
						<span class="activity"><?php bp_message_thread_last_post_date(); ?></span>
					</li>
				<?php else: ?>
					<li class="thread-from">
						<?php _e( 'To:', 'buddyboss' ); ?> <?php bp_message_thread_to(); ?><br />
						<span class="activity"><?php bp_message_thread_last_post_date(); ?></span>
					</li>
				<?php endif; ?>

				<li class="thread-info">
					<p><a href="<?php bp_message_thread_view_link(); ?>" title="<?php esc_attr_e( "View Message", "buddyboss" ); ?>"><?php bp_message_thread_subject(); ?></a></p>
					<p class="thread-excerpt"><?php bp_message_thread_excerpt(); ?></p>
				</li>

				<?php do_action( 'bp_messages_inbox_list_item' ); ?>

				<li class="thread-options">
					<a class="button confirm" href="<?php bp_message_thread_delete_link(); ?>" title="<?php esc_attr_e( "Delete Message", "buddyboss" ); ?>"><?php _e( 'Delete', 'buddyboss' ); ?></a> &nbsp;
                    <span class="checkbox">
                        <input type="checkbox" name="message_ids[]" value="<?php bp_message_thread_id(); ?>" />
                        <label for="message_ids_<?php bp_message_thread_id(); ?>"><span class="btn"></span></label>
                    </span>
				</li>
        </ul>

        <?php endwhile; ?>
    </div>

	<div class="messages-options-nav">
		<?php bp_messages_options(); ?>
	</div><!-- .messages-options-nav -->

	<?php do_action( 'bp_after_member_messages_threads' ); ?>

	<?php do_action( 'bp_after_member_messages_options' ); ?>

<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( 'Sorry, no messages were found.', 'buddyboss' ); ?></p>
	</div>

<?php endif;?>

<?php do_action( 'bp_after_member_messages_loop' ); ?>
