<?php

// Get acces to global variable $wpdb when using a standalone script like this
define( 'SHORTINIT', true );
$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . '/wp-load.php';

// Get ajax data
$request = $_REQUEST;
$query_results = [];

// echo '<pre>'; print_r($request); echo '</pre>';
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
