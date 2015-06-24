<?php if ( ! defined( 'ABSPATH' ) ) exit;?>

<?php if ($event_records = \AlphaSSS\Repositories\AccountingEvent::findBy(['user_id' => get_current_user_id()])):?>

	<link rel="stylesheet" href="../themes/blue/style.css" type="text/css" id="" media="print, projection, screen" />

	<table class="tablesorter" id="myTable" cellspacing="1">             
		<thead>
			<tr> 
				<th><?php _e('Date', 'alphasss');?></th> 
				<th><?php _e('Transaction Description', 'alphasss');?></th> 
				<th><?php _e('Income (Credits)', 'alphasss');?></th> 
				<th><?php _e('Withdrawal (Credits)', 'alphasss');?></th> 
				<th><?php _e('Ending Ballance (Credits)', 'alphasss');?></th> 
			</tr> 
		</thead> 
		<tbody> 
	<?php foreach ($event_records as $event_record):?>
			<tr> 
				<td><?php echo $event_record['event_date']; ?></td> 
				<td><?php echo $event_record['description']; ?></td> 
				<td><?php echo $event_record['income_credits']; ?></td> 
				<td><?php echo $event_record['withdrawal_credits']; ?></td> 
				<td><?php echo $event_record['ballance']; ?></td> 
			</tr>

						<tr> 
				<td><?php echo $event_record['event_date']; ?></td> 
				<td><?php echo $event_record['description']; ?></td> 
				<td>50</td> 
				<td><?php echo $event_record['withdrawal_credits']; ?></td> 
				<td><?php echo $event_record['ballance']; ?></td> 
			</tr> 
	<?php endforeach;?>
	    </tbody> 
	</table>

	<script type="text/javascript">
		$(document).ready(function(){ 
			$("#myTable").tablesorter(); 
		}); 
	</script>
<?php endif;?>

