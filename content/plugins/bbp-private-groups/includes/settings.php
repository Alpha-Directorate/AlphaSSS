<?php

//new file

function rpg_settings_page()
{
global $rpg_settingsf ;
	global $rpg_settingsg ;
	global $rpg_groups;
	global $rpg_group_last ;
	global $rpg_roles ;
	
	
	?>
	<div class="wrap">
		<div id="upb-wrap" class="upb-help">
			<h2><?php _e('Private Group Settings', 'bbp-private-groups'); ?></h2>
			<?php
			if ( ! isset( $_REQUEST['updated'] ) )
				$_REQUEST['updated'] = false;
			?>
			<?php if ( false !== $_REQUEST['updated'] ) : ?>
			<div class="updated fade"><p><strong><?php _e( 'Group saved', 'bbp-private-groups'); ?> ); ?></strong></p></div>
			<?php endif; ?>
			
			<?php //tests if we have selected a tab ?>
			<?php
            if( isset( $_GET[ 'tab' ] ) ) {
				if ($active_tab == 'user_management') pg_user_management($tab) ; 
				$active_tab = $_GET[ 'tab' ];}
			else {$active_tab= 'forum_visibility_settings';
            } // end if
        ?>
		
		<?php // sets up the tabs ?>			
		<h2 class="nav-tab-wrapper">
		
	<a href="?page=bbp-private-group-settings&tab=forum_visibility_settings" class="nav-tab <?php echo $active_tab == 'forum_visibility_settings' ? 'nav-tab-active' : ''; ?>">Forum Visibility settings</a>
	<a href="?page=bbp-private-group-settings&tab=general_settings" class="nav-tab <?php echo $active_tab == 'general_settings' ? 'nav-tab-active' : ''; ?>">General Settings</a>
 	<a href="?page=bbp-private-group-settings&tab=group_name_settings" class="nav-tab <?php echo $active_tab == 'group_name_settings' ? 'nav-tab-active' : ''; ?>">Group Name Settings</a>
	<a href="?page=bbp-private-group-settings&tab=help" class="nav-tab <?php echo $active_tab == 'help' ? 'nav-tab-active' : ''; ?>">Help</a>
	<a href="?page=bbp-private-group-settings&tab=management_information"  class="nav-tab <?php echo $active_tab == 'Management_information' ? 'nav-tab-active' : ''; ?>">Management Information</a>
	<a href="?page=bbp-private-group-settings&tab=user_management"  class="nav-tab <?php echo $active_tab == 'user_management' ? 'nav-tab-active' : ''; ?>">User Management</a>	
	<a href="?page=bbp-private-group-settings&tab=role_assignment"  class="nav-tab <?php echo $active_tab == 'role_assignment' ? 'nav-tab-active' : ''; ?>">Assign groups to roles</a></h2>	
	<table class="form-table">
			<tr>
			
			<td>
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="S6PZGWPG3HLEA">
<input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online.">
<img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
</form>
</td><td>
<?php _e("If you find this plugin useful, please consider donating just a couple of dollars to help me develop and maintain it. You support will be appreciated", 'bbp-last-post'); ?>


</td>
</tr>
</table>

<?php //************************* Forum Visibility settings *************************// ?>

<?php if( $active_tab == 'forum_visibility_settings' ) { ?>
			<form method="post" action="options.php">
			
			<?php settings_fields( 'rpg_forum_settings' ); ?>
			
			<table class="form-table">
			
			<!-------------------------------Forum visibility ---------------------------------------->
			
			<tr valign="top">
						<th><h3><?php _e('Forum Visibility', 'bbp-private-groups'); ?></h3></th>
						<td><p><?php _e('By default only users with access to a forum will see the forum titles in the indexes.  However you may want everyone to see that a forum exists (ie see the title) but not be able to access topics and replies within this.  In this case, set the forum visibility below.  If you want only logged in users to see these forums exist, then also set the forum to private within the dashboard>forums settings', 'bbp-private-groups') ?> <b> See help tab for more information</b></p></td>
			</tr>
			
			<!-- checkbox to activate -->
					<tr valign="top">  
					<th><?php _e('Activate', 'bbp-private-groups'); ?></th>
					<td>
					<?php activate_forum_visibility_checkbox() ;?>
					</td>
					</tr>
					
					<!-------------------------------Redirect Pages ---------------------------------------->
					
					<tr valign="top">
						<th><h3><?php _e('Redirect Pages', 'bbp-private-groups'); ?></h3></th>
						<td><p><?php _e('<b>If you have activated forum visibility above</b>, then users without access will see forums headings and descriptions.  When they click these  forum titles, they need to be sent somewhere, if only to say that they do not have access.  However this is an excellent opportunity to "sign them up" so you can send them to say a register or buy access page. ', 'bbp-private-groups'); ?></p></td>
					</tr>
					
					<tr valign="top">
					<th><?php _e('URL of redirect page for LOGGED-IN user', 'bbp-private-groups'); ?></th>
					<td>
						<input id="rpg_settingsf[redirect_page1]" class="large-text" name="rpg_settingsf[redirect_page1]" type="text" value="<?php echo isset( $rpg_settingsf['redirect_page1'] ) ? esc_html( $rpg_settingsf['redirect_page1'] ) : '';?>" /><br/>
						<label class="description" for="rpg_settingsf[redirect_page]"><?php _e( 'Enter the full url (permalink) of the page to redirect users without access to eg http://www.mysite.com/sign-up.  If you leave this blank, users will see your sites "404 not-found" page', 'bbp-private-groups' ); ?></label><br/>
					</td>
					</tr>
					
					<tr valign="top">
					<th><?php _e('URL of redirect page for NON-LOGGED-IN', 'bbp-private-groups'); ?></th>
					<td>
						<input id="rpg_settingsf[redirect_page2]" class="large-text" name="rpg_settingsf[redirect_page2]" type="text" value="<?php echo isset( $rpg_settingsf['redirect_page2'] ) ? esc_html( $rpg_settingsf['redirect_page2'] ) : '';?>" /><br/>
						<label class="description" for="rpg_settingsf[redirect_page]"><?php _e( 'Enter the full url (permalink) of the page to redirect users without access to eg http://www.mysite.com/sign-up.  This can be the same as the LOGGED-IN page, just giving the opportunity to have different pages if you want them !  If you leave this blank, users will be sent to the wordpress login page', 'bbp-private-groups' ); ?></label><br/>
					</td>
					</tr>
					
					<!-------------------------------Freshness settings ---------------------------------------->
					
					<tr valign="top">
						<th><?php _e('Freshness Settings', 'bbp-private-groups'); ?></th>
						<td><p><?php _e('<b>If you have activated forum visibility above</b>, for private group forums, when user does not have access, you can either show a message in freshness column, or leave it as the default time since last post.  In both cases for users without access they will be taken to the redirect page above. ', 'bbp-private-groups'); ?></p></td>
					</tr>
					
					<!-- checkbox to activate -->
					<tr valign="top">  
					<th><?php _e('Activate', 'bbp-private-groups'); ?></th>
					<td>
					<?php freshness_checkbox() ;?>
					</td>
					</tr>
					
					<tr valign="top">
					<th><?php _e('Freshness Message', 'bbp-private-groups'); ?></th>
					<td>
						<input id="rpg_settingsf[freshness_message]" class="large-text" name="rpg_settingsf[freshness_message]" type="text" value="<?php echo isset( $rpg_settingsf['freshness_message'] ) ? esc_html( $rpg_settingsf['freshness_message'] ) : '';?>" /><br/>
						<label class="description" for="rpg_settingsf[redirect_page]"><?php _e( 'Enter the message to be shown e.g. Click here to sign up', 'bbp-private-groups' ); ?></label><br/>
					</td>
					</tr>
					
					</table>
					
					<!-- save the options -->
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e( 'Save Options', 'bbp-private-groups' ); ?>" />
				</p>
				
				</form>
		</div><!--end sf-wrap-->
	</div><!--end wrap-->
	
	<?php
	}
	?>
	
	<?php //************************* General settings *************************// ?>
	
	<?php if( $active_tab == 'general_settings' ) { ?>
			<form method="post" action="options.php">
			
			<?php settings_fields( 'rpg_general_settings' ); ?>
			
			<table class="form-table">
					<!------------------------------- Hide topic/reply counts ------------------------------------------>
					<tr valign="top">
						<th colspan="2"><h3><?php _e('Hide topic and reply counts', 'bbp-private-groups'); ?></h3></th>
					</tr>
			
			<!-- checkbox to activate -->
					<tr valign="top">  
					<th><?php _e('Activate', 'bbp-private-groups'); ?></th>
					<td>
					<?php activate_hide_counts_checkbox() ;?>
					</td>
					</tr>
					
					<!------------------------------- Descriptions ------------------------------------------>
					<tr valign="top">
						<th colspan="2"><h3><?php _e('Show Descriptions', 'bbp-private-groups'); ?></h3></th>
					</tr>
					
					<!-- checkbox to activate -->
					<tr valign="top">  
					<th><?php _e('Activate', 'bbp-private-groups'); ?></th>
					<td>
					<?php activate_descriptions_checkbox() ;?>
					</td>
					</tr>
					
					<!------------------------------- Remove 'Private' prefix ------------------------------------------>
					<tr valign="top">
						<th colspan="2"><h3><?php _e("Remove 'Private' prefix", 'bbp-private-groups'); ?></h3></th>
					</tr>
					
					<!-- checkbox to activate -->
					<tr valign="top">  
					<th><?php _e('Activate', 'bbp-private-groups'); ?></th>
					<td>
					<?php activate_private_prefix_checkbox() ;?>
					</td>
					</tr>
					<tr valign="top">
					<td></td><td><?php _e('By default bbPress shows the prefix "Private" before each private forum. Activate this checkbox to remove this prefix.', 'bbp-private-groups'); ?></td>
					</tr>
					
					</table>
				
				<!-- save the options -->
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e( 'Save Options', 'bbp-private-groups' ); ?>" />
				</p>
				
				</form>
		</div><!--end sf-wrap-->
	</div><!--end wrap-->
	
	<?php
	}
	?>
	
	
	
	
	<?php //************************* Group name settings *************************// ?>
			<?php if( $active_tab == 'group_name_settings' ) { ?>
			<form method="post" action="options.php">
			
			<?php settings_fields( 'rpg_group_settings' ); ?>
			
			<table class="form-table">
			
			<tr valign="top">
			<th colspan="2"><p> This section allows you to set up groups.  Enter a Description for each group eg gamers, teachers, group1 etc.</p></th>
			</tr>
			<?php 
			$count=count ($rpg_groups) ;
			if ($count==1) $count=2 ;
			for ($i = 0 ; $i < $count ; ++$i) {
			$g=$i+1 ;
			$name="group".$g ;
			$item="rpg_groups[".$name."]" ;
			?>
			<!-------------------------  Group  --------------------------------------------->		
					<tr valign="top">
					<th><?php echo $name ?></th>
					<td>
					<?php echo '<input id="'.$item.'" class="large-text" name="'.$item.'" type="text" value="'.esc_html( $rpg_groups[$name] ).'"<br>' ;
				?>
					</td>
					</tr>
					<?php }
					 			
					?>
					<!-- checkbox to activate new group -->
					<tr valign="top">  
					<th><?php _e('Add new group', 'bbp-private-groups'); ?></th>
					<td>
					<?php activate_new_group() ;?>
					
					</td>
					</tr>
					
					
					</table>
					<!-- save the options -->
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e( 'Save Groups', 'bbp-private-groups' ); ?>" />
				</p>
				</form>
		</div><!--end sf-wrap-->
	</div><!--end wrap-->
	
<?php
}
?>


<?php //************************* Management Info *************************// ?>
			<?php if( $active_tab == 'management_information' ) { 
			?>
					
			<?php settings_fields( 'rpg_group_settings' ); ?>
			
			<table class="form-table">
			
			<tr valign="top">
			
			</tr>
			<?php 
			$count=count ($rpg_groups) ;
			for ($i = 0 ; $i < $count ; ++$i) {
			$g=$i+1 ;
			$name="group".$g ;
			$item="rpg_groups[".$name."]" ;
			?>
			<!-------------------------  Group  --------------------------------------------->		
					<tr valign="top">
					<th><?php echo $name ?></th>
					<td>
					Group name : 
					<?php echo esc_html( $rpg_groups[$name] ).'<br>' ; ?>
					No. users in this group : 
					<?php 
					global $wpdb;
					$users=$wpdb->get_col("select ID from $wpdb->users") ;
					$countu=0 ;
					foreach ($users as $user) {
					
					$check=  get_user_meta( $user, 'private_group',true);
					//single user check
					if ($check==$name) $countu++ ;
					//multiple group set
					if (strpos($check, '*'.$name.'*') !== FALSE) $countu++;
					}
					echo $countu ;
					?>
					

					<br>Forums in this group :
					<?php global $wpdb;
					$forum = bbp_get_forum_post_type() ;
					$forums=$wpdb->get_col("select ID from $wpdb->posts where post_type='$forum'") ;
					$countu=0 ;
					echo '<ul><i>' ;
					foreach ($forums as $forum) {
						$meta = (array)get_post_meta( $forum, '_private_group', false );
						foreach ($meta as $meta2) {
							if ($meta2==$name) {
							$ftitle=bbp_forum_title($forum) ;
							echo '<li>'.$ftitle.'</li>' ;
							$countu++ ;
							}
						}
								
					}
					echo '</ul></i>' ;
					
					echo 'No. forums that have this group set : '.$countu ;
					?>
					</td></tr>
					<?php }
					 			
					?>
													
					</table>
					
				</form>
		</div><!--end sf-wrap-->
	</div><!--end wrap-->
	
<?php
}
?>

<?php
//****  user management
if ($active_tab == 'user_management' ) {
$group = 'all' ;
pg_user_management($group) ;
}
?>

<?php
//****  Help
if ($active_tab == 'help' ) {
pg_help();
}
?>

<?php
//****  role assignment
if ($active_tab == 'role_assignment' ) {
pg_role_assignment() ;
}
//end of tab function
}







// register the plugin settings
function rpg_register_settings() {

	register_setting( 'rpg_forum_settings', 'rpg_settingsf' );
	register_setting( 'rpg_general_settings', 'rpg_settingsg' );
	register_setting( 'rpg_group_settings', 'rpg_groups' );
	register_setting( 'rpg_roles_settings', 'rpg_roles' );
	}
//call register settings function
add_action( 'admin_init', 'rpg_register_settings' );

function rpg_settings_menu() {

	// add settings page
	add_submenu_page('options-general.php', __('bbp Private Groups', 'bbp-private-groups'), __('bbp Private Groups', 'bbp-private-groups'), 'manage_options', 'bbp-private-group-settings', 'rpg_settings_page');
}
add_action('admin_menu', 'rpg_settings_menu');

/*****************************   Checkbox functions **************************/
function activate_forum_visibility_checkbox() {
 	global $rpg_settingsf ;
	$item5 =  $rpg_settingsf['set_forum_visibility'] ;
	echo '<input name="rpg_settingsf[set_forum_visibility]" id="rpg_settingsf[set_forum_visibility]" type="checkbox" value="1" class="code" ' . checked( 1,$item5, false ) . ' /> Click to activate forum visibility';
  }
  function freshness_checkbox() {
 	global $rpg_settingsf ;
	$item4 =  $rpg_settingsf['set_freshness_message'] ;
	echo '<input name="rpg_settingsf[set_freshness_message]" id="rpg_settingsf[set_freshness_message]" type="checkbox" value="1" class="code" ' . checked( 1,$item4, false ) . ' /> Click to activate a freshness message';
  }
  
function activate_hide_counts_checkbox() {
 	global $rpg_settingsg ;
	$item1 =  $rpg_settingsg['hide_counts'] ;
	echo '<input name="rpg_settingsg[hide_counts]" id="rpg_settingsg[hide_counts]" type="checkbox" value="1" class="code" ' . checked( 1,$item1, false ) . ' /> Hide topic and reply counts';
  }
  function activate_descriptions_checkbox() {
 	global $rpg_settingsg ;
	$item2 =  $rpg_settingsg['activate_descriptions'] ;
	echo '<input name="rpg_settingsg[activate_descriptions]" id="rpg_settingsg[activate_descriptions]" type="checkbox" value="1" class="code" ' . checked( 1,$item2, false ) . ' /> Show forum content (Descriptions) on main index';
  }
  function activate_private_prefix_checkbox() {
 	global $rpg_settingsg ;
	$item3 =  $rpg_settingsg['activate_remove_private_prefix'] ;
	echo '<input name="rpg_settingsg[activate_remove_private_prefix]" id="rpg_settingsg[activate_remove_private_prefix]" type="checkbox" value="1" class="code" ' . checked( 1,$item3, false ) . ' /> Remove Private prefix';
  }
function activate_new_group() {
 	global $rpg_groups ;
	$item6 =  $rpg_groups['activate_new_group'] ;
	echo '<input name="rpg_groups[activate_new_group]" id="rpg_groups[activate_new_group]" type="checkbox" value="1" class="code"  /> Click and then press "save groups" to add a new group' ;
  }
  