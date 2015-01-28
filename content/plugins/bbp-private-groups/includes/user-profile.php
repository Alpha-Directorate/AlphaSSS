<?php
function rpg_user_profile_field() {
	 global $current_user;
	 global $rpg_groups ;
	 
		
		
	 if (isset($_REQUEST['user_id'])) {
		$user_id = $_REQUEST['user_id'];
	 } else {
		$user_id = $current_user->ID;
	 }
		?>
	 <table class="form-table">
			<tbody>
				<tr>
					<th><label for="bbp-private-groups"><?php esc_html_e( 'Private Groups', 'bbp-private-groups' ); ?></label></th>
					<td>

						<?php global $rpg_groups ;
							if (empty( $rpg_groups ) ) : ?>
							
							<option value=""><?php esc_html_e( '&mdash; No groups yet set up &mdash;', 'bbp-private-groups' ); ?></option>

							<?php else : ?>
							
							
							<!-- checkbox to activate -->
					<?php $private_group = get_user_meta($user_id, 'private_group', true); ?>
				
					
					<?php foreach ( $rpg_groups as $group => $details ) : ?>
						<tr valign="top">  
						<?php $groupname=__('Group','bbp-private-groups').substr($group,5,strlen($group)) ; ?>
						<th><?php echo $groupname." ".$details ; ?></th>
						<td>
						<?php
						$check=0 ;
						if (strpos($private_group, '*'.$group.'*') !== FALSE) $check=1 ;
						elseif($private_group == 	$group) $check=1 ;
						//echo $check;
						echo '<input name="'.$group.'" id="group" type="checkbox" ' ;
						if( $check ) echo 'checked="checked"'; 
						echo ' />' ;
						_e ('Click to add this group', 'bbp-private-groups' );
						?>
						</td>
						</tr>
						<?php endforeach; ?>
							<?php endif; ?>
							

						</select>
					</td>
				</tr>

			</tbody>
		</table>
		<?php
		
		
		}
		
		
		// User profile edit/display actions
		add_action( 'edit_user_profile', 'rpg_user_profile_field', 50,2 )  ;
		
function bbp_edit_user_pg( $user_id ) {
	global $rpg_groups ;
	$string='*' ;
		foreach ( $rpg_groups as $pggroup => $details) { 
		$item='private_group_'.$pggroup ;
		$data = ($_POST[$pggroup] );
			if ($data=='on') {
			$string=$string.$pggroup.'*' ;
		}
	}
	if ($string=='*') $string = '' ;
	update_user_meta( $user_id, 'private_group', $string);
	}

add_action( 'edit_user_profile_update', 'bbp_edit_user_pg' );

