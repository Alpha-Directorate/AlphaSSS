<?php if ( ! defined( 'ABSPATH' ) ) exit;?>


<?php if ($event_records = \AlphaSSS\Repositories\AccountingEvent::findBy(['user_id' => get_current_user_id()])):?>
	<?php foreach ($event_records as $event_record):?>
		<p><?php echo $event_record['description']; ?></p>
	<?php endforeach;?>
<?php endif;?>

