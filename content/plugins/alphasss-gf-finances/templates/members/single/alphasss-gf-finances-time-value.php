<?php if ( ! defined( 'ABSPATH' ) ) exit;?>

<table class="table" id="time-value-table" cellspacing="1">             
	<thead>
		<tr> 
			<th class="text-center"><?php _e('Check to Edit', 'alphasss');?></th> 
			<th class="text-center"><?php _e('Session Time', 'alphasss');?></th> 
			<th class="text-center"><?php _e('Session Value', 'alphasss');?></th> 
		</tr>
	</thead> 
	<tbody>
		<?php foreach ([30, 45, 60, 90, 120] as $value):?>
		<tr> 
			<td class="text-center"><input class="time-value-checkbox" type="checkbox" /></td> 
			<td class="text-center"><?php printf(__('%d min'), $value); ?></td> 
			<td class="text-center"><a>not used</a></td> 
		</tr>
	<?php endforeach;?>
	</tbody> 
</table>

<script type="text/javascript">
	$(document).ready(function(){
		$('.time-value-checkbox').click(function(){
			if ($(this).attr('checked') == 'checked'){
				$(this).parent('td').next('td').next('td').html('<input value="0" class="input-small" /> credits');
			} else {
				$(this).parent('td').next('td').next('td').html('<a>not used</a>');
			}
		});
	});
</script>