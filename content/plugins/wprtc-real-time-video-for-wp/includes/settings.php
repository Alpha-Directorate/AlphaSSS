<?php
function wprtc_menu() {
    add_menu_page( 'wpRTC', 'wpRTC', 'manage_options', 'wp-rtc', 'wprtc_main_options', 'dashicons-admin-generic', 81 );
    add_submenu_page( 'wp-rtc', 'wpRTC Settings', 'wpRTC Settings', 'manage_options', 'wp-rtc-settings', 'wprtc_settings_options' );
    add_submenu_page( 'wp-rtc', 'wpRTC Styling', 'wpRTC Styling', 'manage_options', 'wp-rtc-css', 'wprtc_css_options' );
}

function wprtc_main_options() {
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    echo '<style> .feature-filter { padding: 20px; } strong { font-weight: bold; }</style>';
    echo '<div class="wrap feature-filter">';
        echo '<h1>wpRTC - Real Time Video Sharing for WordPress</h1>';
        echo '<table width="100%" cellpadding="5" cellspacing="5" border="0">';
            echo '<tr>';
                echo '<td width="50%" valign="top">';
                	echo '<h3>Documentation</h3>';
                	echo '<p> In depth documentation available online <br/>';
                	echo '<a class="button-primary" href="http://www.roysivan.com/wp-webrtc" target="_blank">View Documentation</a></p>';
                    echo '<h3>Shortcode</h3>';
                    echo '<p>The plugin comes with a built in shortcode to help you with setting up your videos easily</p>';
                    echo '<pre><code>[wpRTC]</code></pre>';
                    // ATTS
                    echo '<h4>Shortcode Attributes</h4>';
                    // room_title
                    echo '<p><strong>room_title</strong> - title over your video.<br/>';
                    echo '<code>[wpRTC room_title="..."]</code><br/><em>leave blank to remove title</em></p>';
                    // room_name
                    echo '<p><strong>room_name</strong> - set up your room name or multiple rooms. <br/><code>[wpRTC room_name="testing"]</code> </p>';
                    echo '<p><code>[wpRTC room_name="testing, testingAgain, anotherRoom"]</code> - <em>This feature adds in a drop down menu under the video so users can change rooms using the same page and video code. wpRTC PRO</em></p>';
                    // privacy
                    echo '<p><strong>privacy</strong> - override default settings.<br/>';
                    echo '<code>[wpRTC privacy="on"]</code> - <em>wpRTC PRO</em></p>';
                echo '</td>';
                echo '<td valign="top">';
                    echo '<h3>wpRTC PRO</h3>';
                    echo '<p>Upgrade to wpRTC PRO to get additional features and future release functionality</p>';
                    echo '<h4>Additional Features in wpRTC Pro</h4>';
                    echo '<ul>';
                        echo '<li><strong><a target="_blank" href="http://roysivan.com/wp-webrtc/examples/multiple-rooms/">Multiple Rooms</a></strong> <br/> 1 shortcode, 1 video, multiple rooms</li>';
                        echo '<li><strong><a target="_blank" href="http://roysivan.com/wp-webrtc/examples/privacy/">Privacy Override</a></strong> <br/> Want all your video streams to be sign-in only, except a few?<br/> Override your global setting for privacy on a per video basis.</li>';
                    echo '</ul>';
                    echo '<h3><strong>wpRTC Pro</strong> - is available now starting at just $9.99</h3>';
                    echo '<a class="button-primary" href="http://www.roysivan.com/downloads/wprtc-pro-webrtc-wordpress/" target="_blank">Buy wpRTC Pro</a>';
                echo '</td>';
            echo '</tr>';
        echo '</table>';
    echo '</div>';
}

function wprtc_settings_options() {

    // Handle Saving
    $toSave = array('rtc_main_private', 'rtc_main_private_msg');

    foreach($_POST as $key => $value) {
        if( in_array( $key, $toSave ) ) {
            update_option($key, $value);
        }
    }
    $currentVal = array();
    foreach($toSave as $key) {
        $currentVal[$key] = get_option($key);
    }
    echo '<style> .feature-filter { padding: 20px; } label,strong { font-weight: bold; } select,textarea{ width: 400px; } textarea {height: 200px; }</style>';
    echo '<div class="wrap feature-filter">';
        echo '<h2>wpRTC - Real Time Video Sharing for WordPress</h2>';
        echo '<p>You do not need to make any changes here, default styling is setup.<br/>';
        echo '<strong>Local Video</strong> - your video stream<br/>';
        echo '<strong>Remote Video</strong> - video stream of peers connected to your video room</p>';
        echo '<form name="wprtcSettings" method="post" action="">';
            echo '<p><label>Private Videos</label><br/>';
            echo '<select name="rtc_main_private">';
                echo '<option value="1"';
                    if($currentVal['rtc_main_private'] == '1') echo 'selected="selected"';
                echo '>On</option>';
                echo '<option value="0"';
                    if($currentVal['rtc_main_private'] == '0') echo 'selected="selected"';
                echo '>Off</option>';
            echo '</select><br/><em>Turn this option on if you must be logged in to see video</em></p>';
            if($currentVal['rtc_main_private'] == '1'):
                echo '<p><label>Private Video Message</label><br/>';
                echo '<textarea name="rtc_main_private_msg" placeholder="Videos are private">'.$currentVal['rtc_main_private_msg'].'</textarea>';
                echo '<br/><em>Message displayed when video privacy is turned on</em></p>';
            endif;
        echo '<br/><input type="submit" value="Save Settings" class="button-primary" /></form>';
    echo '</div>';

}

// function resetOptions() {
//     $rtcOptions = array(
//         'rtcBG' => '#000',
//         'rtcBC' => '#000',
//         'rtcBW' => '2px',
//         'rtcW' => '100%',
//         'rtcH' => '500px',
//         'rtcRH' => '200px',
//         'rtcRvW' => '100px',
//     );

//     foreach($rtcOptions as $key => $value) {
//         update_option($key, $value);
//     }
// }

function wprtc_css_options() {
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }

    // Handle Saving
    $toSave = array('rtcBG', 'rtcBC', 'rtcBW', 'rtcW', 'rtcH', 'rtcRH', 'rtcRvW', 'rtcClass');

    // Delete All
    if( isset($_POST['deleteAllwpRTC']) && $_POST['deleteAllwpRTC'] == '1' ) {
        foreach($toSave as $key) {
            delete_option($key);
        }
    }

    foreach($_POST as $key => $value) {
        if( in_array( $key, $toSave ) ) {
            update_option($key, $value);
        }
    }

    $currentVal = array();
    foreach($toSave as $key) {
        $currentVal[$key] = get_option($key);
    }
    echo '<table width="100%" cellpadding="5" cellspacing="0" border="0"><tbody>';
        echo '<tr><td  valign="top"><form name="wprtcStyles" method="post" action="">';
            echo '<div class="tabbedContent">';
                echo '<ul>';
                    echo '<li><a href="#tabLocal">Local Video Styling</a></li>';
                    echo '<li><a href="#tabRemote">Remote Video Styling</a></li>';
                    echo '<li><a href="#tabAdv">Advanced Styling</a></li>';
                echo '</ul>';
                echo '<div id="tabLocal">';
                	echo '<h3>Background Color</h3>';
                    echo '<p><label>Background Color</label><br/>';
                    echo '<input name="rtcBG" placeholder="Video Background" class="color-picker" value="'.$currentVal['rtcBG'].'" /></p><hr/>';
                	echo '<h3>Video Size</h3>';
                    echo '<p><label>Video Width (i.e 500px)</label><br/>';
                    echo '<input name="rtcW" placeholder="Video Width" value="'.$currentVal['rtcW'].'" /></p><hr/>';
                    echo '<p><label>Video Height  (i.e 500px)</label><br/>';
                    echo '<input name="rtcH" placeholder="Video Height" value="'.$currentVal['rtcH'].'" /></p><hr/>';
                	echo '<h3>Video Border</h3>';
                    echo '<p><label>Video Border Color</label><br/>';
                    echo '<input name="rtcBC" placeholder="Border Color" value="'.$currentVal['rtcBC'].'" class="color-picker" /></p><hr/>';
                    echo '<p><label>Video Border Width (i.e 2px)</label><br/>';
                    echo '<input name="rtcBW" placeholder="Border Width" value="'.$currentVal['rtcBW'].'" /></p><hr/>';
                echo '</div>';
                echo '<div id="tabRemote">';
                    echo '<h3>Remove Video Styling</h3>';
                    echo '<p><label>Remote Video Container Height (i.e 150px)</label><br/>';
                    echo '<input name="rtcRH" placeholder="Container Height" value="'.$currentVal['rtcRH'].'" /></p><hr/>';
                    echo '<p><label>Remote Video Width (i.e 150px)</label><br/>';
                    echo '<input name="rtcRvW" placeholder="Video Width" value="'.$currentVal['rtcRvW'].'" /></p><hr/>';
                echo '</div>';
                echo '<div id="tabAdv">';
                	echo '<h3>Advanced Styling</h3>';
                	echo '<p><label>CSS Class for Video Wrapper</label><br/>';
                	echo '<input name="rtcClass" placeholder="CSS class" value="'.$currentVal['rtcClass'].'"/><br/>';
                	echo '<em>This will add an additional class to the rtcVideoContainer video wrapper div</p><hr/>';
                echo '</div>';
            echo '</div>';
        echo '<br/><input type="submit" value="Save All Styling" class="button-primary" /></form>';
        //echo '<form method="post"><input type="hidden" value="1" name="deleteAllwpRTC" /><input type="submit" class="button-primary" value="Reset Defaults" /></form>';
        echo '</td></tr>';
    echo '</tbody></table>';
}
?>