<?php if ( ! defined( 'ABSPATH' ) ) exit;?>

<div id="bp-group-documents-form">
	<table class="table" id="time-value-table" cellspacing="1">             
		<thead>
			<tr> 
				<th class="header text-center"><?php _e('Check to Edit', 'alphasss');?></th> 
				<th class="header text-center"><?php _e('Session Time', 'alphasss');?></th> 
				<th class="header text-center"><?php _e('Session Value', 'alphasss');?></th> 
			</tr>
		</thead> 
		<tbody>
			<?php $time_values = get_user_meta( get_current_user_id(), 'gf_finances_time_values', true ); ?>
			<?php foreach ( $time_values as $time => $value ):?>
			<tr> 
				<td class="text-center"><input class="time-value-checkbox" type="checkbox" /></td> 
				<td class="text-center"><?php printf( __('%d min' ), $time ); ?></td>
				<?php if ( $value > 0 ):?>
					<td class="text-center"><?php echo (int) $value/100; ?></td>
				<?php else :?>
					<td class="text-center"><a class="time-value-link">not used</a></td>
				<?php endif;?>
			</tr>
		<?php endforeach;?>
		</tbody> 
	</table>
</div>

<script type="text/javascript">
	$(document).ready(function(){

		$.post(ajaxurl, {action: 'is_gf_time_values'}, function(data){

			if ( ! data.data.is_gf_time_values){

				$('#profile-alerts').prepend(dangerAlert(
					"<p>" + "<?php _e('Session Values not configured...', 'alphasss');?>" + "</p><br />" + 
					"<p>" + "<?php _e('You currently do not have any talk session values defined. Therefore, you cannot start audio-video. But you can change this in the table below.', 'alphasss');?>" + "</p>", true));
			}
		}, 'json');

		$(window).on('beforeunload', function (e) {
			$('.input-small').each(function(){
				var el   = this;

				var time = $(el).parent('td').prev('td').text();

				update_time(time, el);

				setTimeout(function(){
					console.log('reloaded');
				}, 3000);
			});
		});

		$('.time-value-checkbox').click(function(){
			time = $(this).parent('td').next('td').text();

			var el = $(this);

			if ($(this).attr('checked') == 'checked'){
				$(this).parent('td').next('td').next('td').html(createTimeValueInput(time) + ' credits');
			} else {
				input_value = $(this).parent('td').next('td').next('td').find('.input-small').val();

				get_data = {
					time  : time,
					action: 'get_gf_time_value'
				}

				$.ajax({
					url: ajaxurl, 
					data: get_data,
					dataType: "json",
					success: function(response){
						time_value = response.data.time_value;

						if (time_value > 0) {
		 					el.parent('td').next('td').next('td').text(time_value);
						} else {
							a = $('<a>').addClass('time-value-link').text('not used');
							el.parent('td').next('td').next('td').html(a);
						}
					},
					async: false
				});
			}
		});

		function update_time(time, el)
		{
			var el = $(el);
			var time = time;

			post_data = {
				time  : time,
				value : el.val(),
				action: 'update_gf_time_values'
			}

			$.post(ajaxurl, post_data, function(data){
				get_data = {
					time  : time,
					action: 'get_gf_time_value'
				}

				$.ajax({
					url: ajaxurl, 
					data: get_data,
					dataType: "json",
					success: function(response){
						value = response.data.time_value;

						el.val(value);
					}
				});
			});
		}

		$('.input-small').live({
			focusout: function(){
				var el   = this;
				var time = $(el).parent('td').prev('td').text();

				update_time(time, el);
			},
			input: function() {
				value = $(this).val();

				// if value is float
				if ( value == Number(value) && value%1!==0) {
					// Filter float value
					$(this).val(Number(value.toString().match(/^\d+(?:\.\d{0,2})?/)));
				}
			}
		});

		$('.time-value-link').live('click', function(){
			time = $(this).parent('td').prev('td').text();
			$(this).parent('td').prev('td').prev('td').find('.time-value-checkbox').attr('checked', true);
			$(this).parent('td').html(createTimeValueInput(time) + ' credits');
		});

		function createTimeValueInput(time)
		{
			var input;

			get_data = {
				time  : time,
				action: 'get_gf_time_value'
			}

			$.ajax({
				url: ajaxurl, 
				data: get_data,
				dataType: "json",
				success: function(response){
					value = response.data.time_value;

					input = $('<input value="' + value + '" />').addClass('input-small');
				},
				async: false
			});

			return input.prop('outerHTML');
		}
	});
</script>