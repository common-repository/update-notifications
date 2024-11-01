<?php
/*
Plugin Name: UPDATE NOTIFICATIONS
Plugin URI: http://update-notification.webup.org
Description: Check if your installation of wordpress are updates (core wordpress, themes and plugins). If so send an email.
You can also send a notification to other email addresses besides the administrator. 
You can also disable the receipt of notifications by type (eg core wordpress, plugins, themes).
You can set the length of time that will be in control.
Version: 0.3.4
Author: Mario Spinaci
Author URI: http://update-notification.webup.org
*/

/*  Copyright 2011 PLUGIN_AUTHOR_NAME (email : mariospinaci@webup.org)
 * 
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

require_once  dirname(__FILE__) .'/logic.php';

add_action( 'init', 'update_notification_load_textdomain' );
add_action('upn_send', 'check_and_send');
add_filter('cron_schedules', 'cron_add_minute');
register_activation_hook(__FILE__, 'upn_activation');
register_deactivation_hook(__FILE__, 'upn_deactivation');

if (isset($_GET['activate']) && $_GET['activate'] == 'true') {
    upn_install();
}


function update_notification_load_textdomain() {
	load_plugin_textdomain( 'upn', false, dirname(__FILE__) .'/lang' );
}

function upn_install(){
	set_var();
}

function check_var(){
$c = false;
	if (get_option('upn_data') || get_option('upn_data')== null || get_option('upn_data') == ''){
		delete_option('upn_data');
		$c = true;
	}
	if (get_option('upn_email') || get_option('upn_email')== null || get_option('upn_email') == ''){
		delete_option('upn_email');
		$c = true;
	}
	if (get_option('upn_wp') || get_option('upn_wp')== null || get_option('upn_wp') == ''){
		delete_option('upn_wp');
		$c = true;
	}
	if (get_option('upn_plugins') || get_option('upn_plugins')== null || get_option('upn_plugins') == ''){
		delete_option('upn_plugins');
		$c = true;
	}
	if (get_option('upn_themes') || get_option('upn_themes')== null || get_option('upn_themes') == ''){
		delete_option('upn_themes');
		$c = true;
	}
	if (get_option('upn_check') || get_option('upn_check')== null || get_option('upn_check') == ''){
		delete_option('upn_check');
		$c = true;
	}
	if($c = true){
		set_var();
	}
}

function set_var(){	
		if (!get_option('wun_email'))	add_option('wun_email','');
		if (!get_option('wun_wp'))		add_option('wun_wp','');
		if (!get_option('wun_plugins'))	add_option('wun_plugins','');
		if (!get_option('wun_themes'))	add_option('wun_themes','');
		if (!get_option('wun_check'))	add_option('wun_check','12');
}

function check_and_send(){
	check_var();
	wp_update_plugins();
	wp_update_themes();
	wp_version_check();
	$crea	=	new Logic();
	$crea->sendMail();
}
/* PANEL */

function upn_config_page(){
  if (function_exists('add_options_page')){
    add_options_page('Update Notifications', 
                     'Update Notifications', 
                     8,
                     basename(__FILE__),
                     'upn_admin_config'
                     );
  }
}
add_action('admin_menu', 'upn_config_page');

function upn_admin_config(){
	if(isset($_POST['submit'])){
		upn_deactivation();
		upn_activation();
		if (isset($_POST['upn_email']))	$temp = $_POST['upn_email'];	else	$temp = '';
		$upn_email	=	$temp;
		update_option('wun_email', $temp);
		
		if (isset($_POST['upn_wp']))	$temp = $_POST['upn_wp'];	else	$temp = '0';
		$upn_wp 	=	$temp;
		update_option('wun_wp', $temp);
		
		if (isset($_POST['upn_plugins']))	$temp = $_POST['upn_plugins'];	else	$temp = '0';
		$upn_plugins=	$temp;
		update_option('wun_plugins', $temp);
		
		if (isset($_POST['upn_themes']))	$temp = $_POST['upn_themes'];	else	$temp = '0';
		$upn_themes	=	$temp;
		update_option('wun_themes', $temp);
		
		if (isset($_POST['upn_check']))	$temp = $_POST['upn_check'];	else	$temp = '0';
		$upn_check	=	$temp;
		update_option('wun_check', $temp);
		
	remove_filter('cron_schedules', 'cron_add_minute');
	remove_action('upn_send', 'check_and_send');
	wp_clear_scheduled_hook('upn_send');
	add_filter('cron_schedules', 'cron_add_minute');
	wp_schedule_event(time(), 'upn_time', 'upn_send');
	add_action('upn_send', 'check_and_send');
	
	} else {
		$upn_email 	= 	get_option('wun_email');
		$upn_wp 	=	get_option('wun_wp');
		$upn_plugins=	get_option('wun_plugins');
		$upn_themes	=	get_option('wun_themes');
		$upn_check	=	get_option('wun_check');
	}
	?>
<h2><?php _e('Update Notifications','wp-panel'); ?></h2>
<form method="post" action="options-general.php?page=update-notification.php">
<table class="form-table">
	<tbody>
		<tr valign="top">
			<th scope="row"><label for="upn_email"><?php _e('Added Email','upn')?></label></th>
			<td>
				<input name="upn_email" type="text" id="upn_email" class="regular-text" value="<?php echo($upn_email); ?>">
				<span class="description"><?php _e('To enter multiple email addresses separated by a comma', 'upn'); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="upn_check"><?php _e('Checks for updates every','upn')?></label></th>
			<td>
				<input name="upn_check" type="text" id="upn_check" class="regular-text" value="<?php echo($upn_check); ?>">
				<span class="description"><?php _e('Hours', 'upn'); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Disable notifications for these items', 'upn'); ?></th>
			<td>
				<fieldset>
					<legend class="screen-reader-text"><span><?php _e('Disable notifications for these items', 'upn'); ?></span></legend>
					<label for="default_pingback_flag">
						<input name="upn_wp" type="checkbox" id="upn_wp" value="1" <?php if($upn_wp) echo 'checked="checked"';?>>
						<?php _e('Wordpress Core', 'upn'); ?></label><br>
					<label for="default_ping_status">
						<input name="upn_plugins" type="checkbox" id="upn_plugins" value="1" <?php if($upn_plugins) echo 'checked="checked"';?>>
						<?php _e('Plugins', 'upn'); ?></label><br>
					<label for="default_comment_status">
						<input name="upn_themes" type="checkbox" id="upn_themes" value="1" <?php if($upn_themes) echo 'checked="checked"';?>>
						<?php _e('Themes', 'upn'); ?></label><br>
				</fieldset>
			</td>
		</tr>
	</tbody>
</table>
<p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e('Save', 'upn'); ?>"></p>
</form>
<?php 	
}

/* AGGIUNGE SCHEDULE MINUTE */

function cron_add_minute( $schedules ) {
	// Adds once weekly to the existing schedules.
	$schedules['upn_time'] = array(
		'interval' => 3600 * (int)get_option('wun_check'),
		'display' => 'upn_time'
	);
	return $schedules;
}

/* CRON */
function upn_activation()	{	wp_schedule_event(time(), 'upn_time', 'upn_send');	}
function upn_deactivation()	{	wp_clear_scheduled_hook('upn_send');	}


?>