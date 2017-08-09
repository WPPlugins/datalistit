<?php
//if uninstall not called from WordPress exit

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();

// For Single site
if ( !is_multisite() ) 
{
    global $wpdb, $table_prefix;

    $options_names = $wpdb->get_col( "SELECT option_name FROM $wpdb->options where option_name like 'dli_%'" );
    
	foreach ( $options_names as $option_name ) 
    {
		delete_option( $option_name );
	}

	$tables = $wpdb->get_results("SHOW TABLES LIKE '$table_prefix.dli_%'", ARRAY_N);
	foreach ( $tables as $table ) 
	{
		$wpdb->query( "drop table ".$table[0] );
	}
} 
?>
