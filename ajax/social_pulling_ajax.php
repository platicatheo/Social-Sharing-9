<?php

error_reporting(-1);
ini_set('display_errors', 'On');

// Get acces to global variable $wpdb when using a standalone script like this
$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . '/wp-load.php';

require_once('../social_sharing_9.php');


// Get placement
$response['placement'] = array();

foreach($social_sharing_9->get_placement_values() as $placement_value) {
	
	if($placement_value == 'floating_left') {
		$response['placement'][] = 'floating_left';
	}

	if($placement_value == 'inside_featured_image') {
		$response['placement'][] = 'inside_featured_image';
	}

	if($placement_value == 'below_post_title') {
		$response['placement'][] = 'below_post_title';
	}
	
}

$response['content'] = $social_sharing_9->do_fe_social_icons('');
die( json_encode($response) );
