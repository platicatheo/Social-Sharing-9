<?php
/*
Plugin Name: Social Sharing 9
Plugin URI:  https://webloungedesign.com
Description: This is a customizable Social Sharing plugin for WordPress.
Version:     1.0
Author:      Theo Platica
Author URI:  https://webloungedesign.com
Text Domain: social_sharing_9
Domain Path: /languages
License:     GPL2
 
Social Sharing 9 is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Social Sharing 9 is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
*/



// error_reporting(-1);
// ini_set('display_errors', 'On');



//This line blocks direct access to the this file
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


if ( ! class_exists( 'Social_sharing_9' ) ) {

	/**
	 * Class for Social Sharing 9 plugin
	 */
	class Social_sharing_9 {


		function __construct() {

			// Load plugin text-domain
			add_action( 'init', array($this, 'myplugin_load_textdomain') );

			// Create table when activate
			// To delete the table, a file "uninstall.php" was created in the root folder of the plugin
			register_activation_hook( __FILE__, array($this,'my_plugin_create_table') );

			// register_uninstall_hook( __FILE__, array($this,'my_plugin_remove_table') );

			// Create Admin Menu page
			add_action('admin_menu', array($this, 'create_menu_page') );

			// Add Plugin Action Links
			add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), array($this, 'plugin_action_links') );

			// Include css and js files for Admin Side
			add_action( 'admin_enqueue_scripts', array($this, 'admin_enqueue_styles_and_scripts_ss9') );

			// Create shortcode
			add_shortcode( 'social-sharing-9', array($this, 'do_fe_social_icons') );

			// Social Saving Ajax Hooks - BE
			add_action( 'wp_ajax_social_saving_ajax',  array($this, 'social_saving_ajax') );

			// Inclde css and js files for Front End
			add_action( 'wp_enqueue_scripts', array($this, 'front_end_enqueue_styles_and_scripts_ss9') );

			// Do FE Socials placement function
			add_action( 'wp', array($this, 'do_fe_socials_placement') );

			// Social Puling Ajax Hooks - FE
			add_action( 'wp_ajax_social_pulling_ajax',  array($this, 'social_pulling_ajax') );
			add_action( 'wp_ajax_nopriv_social_pulling_ajax',  array($this, 'social_pulling_ajax') );

		}



		// Social Puling Ajax - FE
		function social_pulling_ajax() {
			$response['placement'] = array();

			foreach($this->get_placement_values() as $placement_value) {
				$response['placement'][] = $placement_value;
			}

			$response['content'] = $this->do_fe_social_icons('');
			die( json_encode($response) );	
		}





		// Load plugin text-domain function
		function myplugin_load_textdomain() {
		  load_plugin_textdomain( 'social_sharing_9' ); 
		}



		// Create table when activate
		function my_plugin_create_table() {

			global $wpdb;
			$charset_collate = $wpdb->get_charset_collate();
			$table_name = $wpdb->prefix . 'social_sharing_9';

			$sql = "CREATE TABLE $table_name (
				id int NOT NULL AUTO_INCREMENT,
				social_name varchar(255) NOT NULL,
				display_order int,
				social_color varchar(255),
				social_status varchar(500),
				PRIMARY KEY  (id)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
			update_option('scial_sharing_9_plugin_version', '1.0');
		}
		


		// Create Admin Menu page
		function create_menu_page() {
			add_menu_page('Social Sharing 9 Settings', 'Social Sharing 9', 'manage_options', 'social-sharing-9-settings', array($this, 'do_menu_page'), 'dashicons-format-status');
		}


		// Add plugin action links
		function plugin_action_links( $links ) {
			$links = array_merge( array(
				'<a href="' . esc_url( admin_url( 'admin.php?page=social-sharing-9-settings' ) ) . '">' . __( 'Settings', 'social_sharing_9' ) . '</a>'
			), $links );
			return $links;
		}



		// This function returns the HTML for the Admin Page
		function do_menu_page()
		{
			ob_start();
			$html = '';
			?>

			<p class="success_ajax">
			<?= __('Your preferences were successfully saved.', 'social_sharing_9'); ?></p>
			<p class="error_ajax"><?= __('A problem occoured while trying to save.', 'social_sharing_9'); ?></p>

			<h1 class="scsh9"><?= __('Social Sharing 9 plugin', 'social_sharing_9'); ?></h1>


			<p class="sorting_paragraph">
				<?= __('*** To change the order of the social icons, drag and drop them in your desired order.', 'social_sharing_9'); ?><br />
				<?= __('*** To display the soocial icons in their original color, clear the color box.', 'social_sharing_9'); ?>
			</p>

			<form class="scsh9 all_fields_form">


				<!-- Social Links, their colors, urls and their enabling states. -->
				<!-- To change their order, drag them. -->
				<div class="sortable_top_labels">
					<label class="name"><?= __('Name', 'social_sharing_9'); ?></label>
					<label class="color"><?= __('Color', 'social_sharing_9'); ?></label>
					<label class="state"><?= __('Active/Inactive', 'social_sharing_9'); ?></label>
				</div>

				<?php $this->do_admin_socials_values(); ?>

				<!-- Post Types Inputs -->
				<label class="general_label"><?= __('Choose the post types where you wish to display the social icons.', 'social_sharing_9'); ?></label>
				<select multiple name="post_types[]">
				<?php foreach(get_post_types() as $post_type)
					{
						echo '<option '.$this->compare_post_type_values($post_type).'>'.$post_type.'</option>';
					}
				?>
				</select> 

				<!-- Size of Social Icons -->
				<label class="general_label"><?= __('Click to choose the size of the social icons', 'social_sharing_9'); ?></label>
				<div class="size_options_container">
					<div class="radio_container">
						<input type="radio" name="social_size" value="small" <?= $this->compare_size_values('small'); ?> />
						<p><?= __('Small', 'social_sharing_9'); ?></p>
					</div>
					<div class="radio_container">
						<input type="radio" name="social_size" value="medium" <?= $this->compare_size_values('medium'); ?> />
						<p><?= __('Medium', 'social_sharing_9'); ?></p>
					</div>
					<div class="radio_container">
						<input type="radio" name="social_size" value="big" <?= $this->compare_size_values('big'); ?> />
						<p><?= __('Big', 'social_sharing_9'); ?></p>
					</div>
				</div>


				<!-- Placement of Social Icons -->
				<label class="general_label"><?= __('Choose where you want to place the social bar.', 'social_sharing_9'); ?></label>
				<select multiple name="placement[]">
				  <option calue="NOWHERE" <?= $this->compare_placement_values("NOWHERE"); ?> ><?= __('NOWHERE', 'social_sharing_9') ?></option>
				  <option value="below_post_title" <?= $this->compare_placement_values("below_post_title"); ?> ><?= __('-Below the Post Title.', 'social_sharing_9') ?></option>
				  <option value="floating_left" <?= $this->compare_placement_values("floating_left"); ?> ><?= __('-Floating on the left area', 'social_sharing_9') ?></option>
				  <option value="after_post_content" <?= $this->compare_placement_values("after_post_content"); ?> ><?= __('-After the post content', 'social_sharing_9') ?></option>
				  <option value="inside_featured_image" <?= $this->compare_placement_values("inside_featured_image"); ?> ><?= __('-Inside the featured image', 'social_sharing_9') ?></option>
				</select>


				<button type="button" class="submit""><?= __('SAVE SETTINGS', 'social_sharing_9'); ?></button>


			</form>

			<h2 class="ss9_plugin_note green"><?= __('<span>Feature:</span> This plugin also creates a shortcode that you can use anywhere you chose: <span>[social-sharing-9]</span>', 'social_sharing_9'); ?></h2>

			<h3 class="ss9_plugin_note red"><?= __('<span>Note:</span> This plugin was created with WordPress\' default functionality in mind. The theme that you\'re using might contain changes that will make the plugin work differently than it\'s supposed to. For additional project based changes, don\'t hesitate to contact the plugin developer.', 'social_sharing_9'); ?></h3>

			<h2 class="scsh9">This is a free plugin, designed and developed by <a href="https://webloungedesign.com" target="_blank">Theo Platica.</a></h2>

			<?php
			$html .= ob_get_contents();

			ob_end_clean();

			echo $html;

		}




		//Function to output Admin Social Values HTML
		function do_admin_socials_values()
		{

			$socials_values = $this->get_social_values();


			if( count($socials_values) )
			{
				echo '<ul id="sortable">';
				foreach($socials_values as $social_value)
				{
					$social_checked_state = $social_value->social_status ? 'checked' : '';

					echo '				
						<li class="ui-state-default">
							<span class="sortable_handle fa fa-arrows"></span>
							<label class="">'.$social_value->social_name.'</label>
							<div class="colorpicker_container">
								<input type="text" class="my-color-field" value="'.$social_value->social_color.'" />
							</div>
							<input type="hidden" name="social_name" value="'.$social_value->social_name.'">
							<input type="checkbox" class="status_checkbox" '.$social_checked_state.'/>	
						</li>
						';
				}
				echo '</ul>';
			}
			else
			{
				echo '
					<ul id="sortable">
						<li class="ui-state-default">
							<span class="sortable_handle fa fa-arrows"></span>
							<label class="">Facebook</label>
							<div class="colorpicker_container">
								<input type="text" class="my-color-field" />
							</div>
							<input type="hidden" name="social_name" value="Facebook">
							<input type="checkbox" class="status_checkbox"/>
						</li>
						<li class="ui-state-default">
							<span class="sortable_handle fa fa-arrows"></span>
							<label class="">Twitter</label>
							<div class="colorpicker_container">
								<input type="text" class="my-color-field" />
							</div>
							<input type="hidden" name="social_name" value="Twitter">
							<input type="checkbox" class="status_checkbox" />
						</li>
						<li class="ui-state-default">							
							<span class="sortable_handle fa fa-arrows"></span>
							<label class="">Google+</label>
							<div class="colorpicker_container">
								<input type="text" class="my-color-field" />
							</div>
							<input type="hidden" name="social_name" value="Google+">	
							<input type="checkbox" class="status_checkbox" />
						</li>
						<li class="ui-state-default">							
							<span class="sortable_handle fa fa-arrows"></span>
							<label class="">Pinterest</label>
							<div class="colorpicker_container">
								<input type="text" class="my-color-field" />
							</div>
							<input type="hidden" name="social_name" value="Pinterest">	
							<input type="checkbox" class="status_checkbox" />
						</li>
						<li class="ui-state-default">							
							<span class="sortable_handle fa fa-arrows"></span>
							<label class="">LinkedIn</label>
							<div class="colorpicker_container">
								<input type="text" class="my-color-field" />
							</div>
							<input type="hidden" name="social_name" value="LinkedIn">	
							<input type="checkbox" class="status_checkbox" />
						</li>
						<li class="ui-state-default">							
							<span class="sortable_handle fa fa-arrows"></span>
							<label class="">Whatsapp</label>
							<div class="colorpicker_container">
								<input type="text" class="my-color-field" />
							</div>
							<input type="hidden" name="social_name" value="Whatsapp">	
							<input type="checkbox" class="status_checkbox" />
						</li>
					</ul>
					';
			}
		}




		// Social Saving Ajax - BE
		function social_saving_ajax() {

			// Get ajax data
			$request = $_REQUEST['social_data'];
			$query_results = [];

			// echo '<pre>'; print_r($request); echo '</pre>';

			global $wpdb;
			$table_name = $wpdb->prefix.'social_sharing_9';


			$i = 1;


			foreach( $request as $key=>$val )
			{
				// Repeat this 6 times (6 socials). It's like if($key == 'Facebok' || 'Twitter' || .....)
				if($i <=6)
				{

					// Check if the social exists to determine if we do update or insert
					$already_exists = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE social_name = '$key'" );

					if(!$already_exists)
					{
						$query_results[] =  $wpdb->insert(
													$table_name, 
													array( 
														'social_name' => $key,
														'display_order' => $val['order_id'],
														'social_color' => $val['color'],
														'social_status' => $val['status']
													)
												);
					}
					else {
							$query_results[] = $wpdb->update( 
												$table_name, 
												array( 
													'display_order' => $val['order_id'],
													'social_color' => $val['color'],
													'social_status' => $val['status']
												), 
												array( 'social_name' => $key )
											);				
					}
				}
				// Increment $i for repetition
				$i++;



				if($key == 'post_types')
				{
					$post_types_array = implode(',', $val);

					// Check if the social exists to determine if we do update or insert
					$already_exists = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE social_name = '$key'" );

					if(!$already_exists)
					{
						$query_results[] =  $wpdb->insert(
													$table_name, 
													array( 
														'social_name' => $key,
														'social_status' => $post_types_array
													)
												);
					}
					else {
							$query_results[] = $wpdb->update( 
												$table_name, 
												array( 'social_status' => $post_types_array	),
												array( 'social_name' => $key )
											);		
					}		

				}



				if($key == 'placement')
				{
					$placement_array = implode(',', $val);

					// Check if the social exists to determine if we do update or insert
					$already_exists = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE social_name = '$key'" );

					if(!$already_exists)
					{
						$query_results[] =  $wpdb->insert(
													$table_name, 
													array( 'social_name' => $key,
														'social_status' => $placement_array
													)
												);
					}
					else {
							$query_results[] = $wpdb->update( 
												$table_name, 
												array( 'social_status' => $placement_array ),
												array( 'social_name' => $key )
											);		
					}		

				}



				if($key == 'size')
				{
					// Check if the social exists to determine if we do update or insert
					$already_exists = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE social_name = '$key'" );

					if(!$already_exists)
					{
						$query_results[] =  $wpdb->insert(
													$table_name, 
													array( 'social_name' => $key,
														'social_status' => $val
													)
												);
					}
					else {
							$query_results[] = $wpdb->update( 
												$table_name, 
												array( 'social_status' => $val ),
												array( 'social_name' => $key )
											);		
					}		

				}

			} // end of foreach


			// Check if the Queries were ok
			$flag = 'success';

			foreach($query_results as $key=>$val)
			{
				if($val !== 0 && $val !== 1)
				{
					$flag = 'error';
				}
			}

			die( $flag );

		}




		// Function to display Social Icons in Front End
		function do_fe_social_icons( $content )
		{
			global $wp;

			$socials_values = $this->get_social_values();


			if( count($socials_values) )
			{
				ob_start();
				?>
				
				<ul class="social_icons_container">


				<?php
				foreach($socials_values as $social_value)
				{
					$social_icon = '';
					$social_color = $social_value->social_color;
					$social_data = '';
					switch($social_value->social_name)
					{
						case 'Facebook':
							$social_icon = '<i class="fa fa-facebook-official" aria-hidden="true"></i>';
							$social_color = $social_color ? $social_color : '#365899';
							$social_status = 'http://www.facebook.com/sharer.php?u='.home_url(add_query_arg(array(),$wp->request));
							break;
						case 'Twitter':
							$social_icon = '<i class="fa fa-twitter-square" aria-hidden="true"></i>';
							$social_color = $social_color ? $social_color : '#1da1f2';
							$social_status = 'https://twitter.com/home?status='.home_url(add_query_arg(array(),$wp->request));
							break;
						case 'Google+':
							$social_icon = '<i class="fa fa-google-plus-square" aria-hidden="true"></i>';
							$social_color = $social_color ? $social_color : '#db4437';
							$social_status = 'https://plus.google.com/share?url='.home_url(add_query_arg(array(),$wp->request));
							break;
						case 'Pinterest':
							$social_icon = '<i class="fa fa-pinterest-square" aria-hidden="true"></i>';
							$social_color = $social_color ? $social_color : '#bd081c';
							$social_status = 'https://pinterest.com/pin/create/button/?url='.home_url(add_query_arg(array(),$wp->request)).'&media=&description=';
							break;
						case 'LinkedIn':
							$social_icon = '<i class="fa fa-linkedin-square" aria-hidden="true"></i>';
							$social_color = $social_color ? $social_color : '#0077b5';
							$social_status = 'http://www.linkedin.com/shareArticle?mini=true&amp;url='.home_url(add_query_arg(array(),$wp->request));
							break;
						case 'Whatsapp':
							$social_icon = '<i class="fa fa-whatsapp" aria-hidden="true"></i>';
							$social_color = $social_color ? $social_color : '#25d366';
							$social_status = 'whatsapp://send?text='.home_url(add_query_arg(array(),$wp->request));
							$social_data = ' data-action="share/whatsapp/share" ';
							break;
					}

					if( $social_value->social_status )
					{
						if(!$social_data) {
							echo '<li>
									<a href="'.$social_status.'"'.$social_data.'style="color: '.$social_color.';">'.$social_icon.'</a>
								  </li>';							
						}
						else {
							if ( $this->check_mobile_and_tablet_version() ) {
								echo '<li>
										<a href="'.$social_status.'"'.$social_data.'style="color: '.$social_color.';">'.$social_icon.'</a>
									  </li>';									
							}
						}
					}
				}
				?>

				</ul>

				<?php

				$html = ob_get_contents();
				ob_end_clean();

				return $content.$html;

			}

		}





		// Include css and js files for Admin Side
		function admin_enqueue_styles_and_scripts_ss9() {
			
			$ajax_url = admin_url( 'admin-ajax.php' );

			// Enqueue Clor Picker
			if( !wp_script_is( 'wp-color-picker', 'enqueued' ) ) {
		    	wp_enqueue_style('wp-color-picker' );
		    }
	    // Enqueue Jquery UI Sortable
			if( !wp_script_is( 'jquery-ui-sortable', 'enqueued' ) ) {
				wp_enqueue_script( 'jquery-ui-sortable' );
			}
			// Enqueue Font Awesome
			wp_enqueue_style('socials-font-awesome', plugins_url('/include/font-awesome-4.7.0/css/font-awesome.min.css', __FILE__));
			// Enqueue Admin CSS & JS
			wp_enqueue_style('socials-custom-css', plugins_url('/css/admin.css', __FILE__ ));
	    wp_enqueue_script( 'socials-custom-js', plugins_url('/js/admin_functions.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
	    wp_localize_script( 'socials-custom-js', 'ajax_url', $ajax_url );
		}




		// Function for fetching Placement values for Social icons from DB
		function get_placement_values()
		{
			global $wpdb;
			$table_name = $wpdb->prefix.'social_sharing_9';

			$placement_values = $wpdb->get_results( 
				"
				SELECT social_status 
				FROM $table_name
				WHERE social_name = 'placement'
				"
			);

			if( count($placement_values) ) {
				$placement_values = $placement_values[0]->social_status;

				$placement_values = explode(',', $placement_values);

				return $placement_values;				
			}

		}


		// Function for fetching Post Type Values from DB
		function get_post_type_values()
		{
			global $wpdb;
			$table_name = $wpdb->prefix.'social_sharing_9';

			$post_type_values = $wpdb->get_results( 
				"
				SELECT social_status 
				FROM $table_name
				WHERE social_name = 'post_types'
				"
			);

			if( count($post_type_values) ) {
				$post_type_values = $post_type_values[0]->social_status;

				$post_type_values = explode(',', $post_type_values);

				return $post_type_values;
			}
		}




		// Function for fetching Size Values from DB
		function get_size_value()
		{
			global $wpdb;
			$table_name = $wpdb->prefix.'social_sharing_9';

			$size_value = $wpdb->get_results( 
				"
				SELECT social_status 
				FROM $table_name
				WHERE social_name = 'size'
				"
			);

			if( count($size_value) ) {
				$size_value = $size_value[0]->social_status;
				return $size_value;
			}
		}




		// Function to compare Size values for Social icons
		function compare_size_values($value)
		{

			if($value == $this->get_size_value())
			{
				return 'checked';
			}
		}



		// Function to compare Placement values for Social icons
		function compare_placement_values($value)
		{
			$placement_values = $this->get_placement_values();
			if( count($placement_values) ) {
				foreach($placement_values as $placement_value)
				{
					if($value == $placement_value)
					{
						return 'selected';
					}
				}				
			}
		}




		// Function to compare Post Types values to display Social icons
		function compare_post_type_values($value)
		{
			
			foreach($this->get_post_type_values() as $post_type_value)
			{
				if($value == $post_type_value)
				{
					return 'selected';
				}
			}
		}



		// Function to fetch Social Values from DB
		function get_social_values()
		{

			global $wpdb;
			$table_name = $wpdb->prefix.'social_sharing_9';

			$socials_values = $wpdb->get_results( 
				"
				SELECT * 
				FROM $table_name
				WHERE social_name not in ('placement', 'size', 'post_types')
				"
			);


			// Sort array by display_order
			if( count($socials_values) )
			{
				$socials_values_sorted = array();
				foreach($socials_values as $key => $value) {
				  $socials_values_sorted[$key] = $value->display_order;
				}
				array_multisort($socials_values_sorted, SORT_ASC, $socials_values);
			}

			// echo '<pre>'; print_r($socials_values); echo '</pre>';

			return $socials_values;

		}




		// Add Social Sharing 9 Icons inside featured image
		function inside_featured_img_social9( $html, $post_id, $post_image_id ) {
			$html = '<div class="inside_featured_img_social9 '.$post_id.'"></div>' . $html;
			return $html;
		}




		// Do FE Socials placement function
		function do_fe_socials_placement()
		{	

			$post_type_values = $this->get_post_type_values();

			if( count($post_type_values) ) {
				foreach($post_type_values as $post_type_value)
				{
					// Check Post Type
					if( get_post_type() == $post_type_value)
					{
						foreach($this->get_placement_values() as $placement_value)
						{
							// Check Placement Values
							if($placement_value == 'after_post_content')
							{	
								if( !is_archive() && !is_home() ) {  // Skip Archive Pages and Blog Page
									add_filter('the_content',  array($this, 'do_fe_social_icons') ); 									
								}
							}
							if($placement_value == 'inside_featured_image')	{	
								add_filter( 'post_thumbnail_html', array($this, 'inside_featured_img_social9'), 10, 3 );
							}
							if($placement_value == 'below_post_title')
							{	
								// add_filter('the_title', array($this, 'change_title'), 10, 2); 
								// echo '<pre>'; var_dump( is_archive() ); echo '</pre>';

								function final_output($content) {
									return apply_filters('final_output', $content);
								}

								ob_start('final_output');
								add_filter('final_output', array($this, 'final_output_title') );
							}
						}
					}
				}				
			}

		}


		// Replace Output pieces inside the content
		function final_output_title($content) {
			return str_replace('>'.get_the_title().'<', '>'.get_the_title().'<span class="socials_title_span"></span><', $content);
		}





		// Inclde css and js files for Front End
		function front_end_enqueue_styles_and_scripts_ss9() {

			global $post;

			$ajax_url = admin_url( 'admin-ajax.php' );

			// Include Jquery
			if( !wp_script_is( 'jquery', 'enqueued' ) ) {
				wp_enqueue_script('jquery');
			}

			// Include font awesome
			wp_enqueue_style('socials-font-awesome-fe', plugins_url('/include/font-awesome-4.7.0/css/font-awesome.min.css', __FILE__));

			// Include default css file
			wp_enqueue_style( 'social_sharing_9_fe', plugins_url('/css/fe.css', __FILE__ ) );

			// Include Size css file
			$size_value = $this->get_size_value();
			if( $size_value == 'medium' )
			{
				wp_enqueue_style( 'social_sharing_9_mmedium_fe', plugins_url('/css/medium.css', __FILE__) );
			}
			if( $size_value == 'big' )
			{
				wp_enqueue_style( 'social_sharing_9_big_fe', plugins_url('/css/big.css', __FILE__) );
			}	

			// Include JS with ajax for Social Icons inside the featured image
			wp_enqueue_script( 'jquery_for_feplacement', plugins_url('/js/placement/place.js', __FILE__));
			// Send params to JS script
			wp_localize_script('jquery_for_feplacement', 'fe_localize_params', array(
																													'ajax_url'=>$ajax_url,
																													'post_id' =>$post->ID
																												)
												);

		}




		// Function that checks 
		function check_mobile_and_tablet_version() {

			$mobile_or_tablet_browser = 0;

			if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
			    $mobile_or_tablet_browser++;
			}
			 
			if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
			    $mobile_or_tablet_browser++;
			}
			 
			if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
			    $mobile_or_tablet_browser++;
			}
			 
			$mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
			$mobile_agents = array(
			    'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
			    'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
			    'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
			    'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
			    'newt','noki','palm','pana','pant','phil','play','port','prox',
			    'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
			    'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
			    'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
			    'wapr','webc','winw','winw','xda ','xda-');
			 
			if (in_array($mobile_ua,$mobile_agents)) {
			    $mobile_or_tablet_browser++;
			}
			 
			if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'opera mini') > 0) {
			    $mobile_or_tablet_browser++;
			    //Check for tablets on opera mini alternative headers
			    $stock_ua = strtolower(isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA'])?$_SERVER['HTTP_X_OPERAMINI_PHONE_UA']:(isset($_SERVER['HTTP_DEVICE_STOCK_UA'])?$_SERVER['HTTP_DEVICE_STOCK_UA']:''));
			    if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/i', $stock_ua)) {
			      $mobile_or_tablet_browser++;
			    }
			}

			return $mobile_or_tablet_browser;
		} 


	}

}

$social_sharing_9 = new Social_sharing_9();

