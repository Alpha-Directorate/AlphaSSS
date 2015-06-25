<?php if ( ! defined( 'ABSPATH' ) ) exit;?>

	<script id="template" type="x-tmpl-mustache">
		<table class="tablesorter table-striped" cellspacing="1">             
			<thead>
				<tr> 
					<th><?php _e('Date', 'alphasss');?></th> 
					<th><?php _e('Transaction Description', 'alphasss');?></th> 
					<th><div><?php _e('Income', 'alphasss');?></div><div class="text-center"><small><?php _e('(Credits)');?></small></div></th> 
					<th><div><?php _e('Withdrawal', 'alphasss');?></div><div class="text-center"><small><?php _e('(Credits)');?></small><div></th> 
					<th><div><?php _e('Ending Ballance', 'alphasss');?></div><div class="text-center"><small><?php _e('(Credits)');?></small><div></th> 
				</tr>
			</thead> 
			<tbody>
				{{#records}}
					<tr> 
						<td>{{ display_date }}</td> 
						<td>{{ description }}</td> 
						<td class="text-right">{{ income_credits }}</td> 
						<td class="text-right">{{ withdrawal_credits }}</td> 
						<td class="text-right">{{ ballance }}</td> 
					</tr>
				{{/records}}
			</tbody> 
		</table>
	</script>

	<div id="my-ccountting_events"></div>

	<script type="text/javascript">
		$(document).ready(function(){
			var template = $('#template').html();
			Mustache.parse(template);

			// Get timezone from browser
			tz = jstz.determine(); 

			$.post(ajaxurl, {'timezone_name': tz.name(), 'action': 'get_gf_accountiong_events'}, function(data){
			 	records = data.data.event_records;

			 	$('#my-ccountting_events').html(Mustache.render(template, {records: records}));

				$("#my-ccountting_events table").tablesorter({sortList: [[0,1]]}); 
			});
		}); 
	</script>

