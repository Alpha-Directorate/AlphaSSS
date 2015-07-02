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
		<?php $time_values = get_user_meta( get_current_user_id(), 'gf_finances_time_values', true ); ?>
		<?php foreach ( $time_values as $time => $value ):?>
		<tr> 
			<td class="text-center"><input class="time-value-checkbox" type="checkbox" /></td> 
			<td class="text-center"><?php printf( __('%d min' ), $time ); ?></td>
			<?php if ( $time > 0 ):?>
				<td class="text-center"><?php echo $value; ?></td>
			<?php else :?>
				<td class="text-center"><a class="time-value-link">not used</a></td>
			<?php endif;?>
		</tr>
	<?php endforeach;?>
	</tbody> 
</table>

<script type="text/javascript">
	$(document).ready(function(){
		$('.time-value-checkbox').click(function(){
			if ($(this).attr('checked') == 'checked'){
				$(this).parent('td').next('td').next('td').html(createTimeValueInput() + ' credits');
			} else {
				input_value = $(this).parent('td').next('td').next('td').find('.input-small').val();

				if (input_value > 0) {
 					$(this).parent('td').next('td').next('td').text(input_value);
				} else {
					a = $('<a>').addClass('time-value-link').text('not used');
					$(this).parent('td').next('td').next('td').html(a);
				}
			}
		});

		$('.input-small').change(function(){
			
		});

		$('.time-value-link').live('click', function(){
			$(this).parent('td').prev('td').prev('td').find('.time-value-checkbox').attr('checked', true);
			$(this).parent('td').html(createTimeValueInput() + ' credits');
		});

		function createTimeValueInput()
		{
			input = $('<input value="0" />').addClass('input-small');

			return input.prop('outerHTML');
		}
	});
</script>