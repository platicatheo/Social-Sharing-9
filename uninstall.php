<?php

global $wpdb;
$table_name =  $wpdb->prefix."social_sharing_9";
$sql = "DROP TABLE IF EXISTS $table_name;";
$wpdb->query($sql);
delete_option("scial_sharing_9_plugin_version");