<?php
/*
 Plugin Name: Datalist it
 Plugin URI: http://datalistit.com
 Description: Put you csv file into a database table and display it on your webside or in your blog.
 Version: 0.0.3
 Author: datalistit
 Author URI: http://www.datalistit.com
 */
?>
<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE);

define("DIR_TABLES", dirname(__FILE__).DIRECTORY_SEPARATOR."tables".DIRECTORY_SEPARATOR);
define("URL", "https://datalistit.com/myreports/Uploader/WPUpload?WP=0.0.3");

define( 'D_ABSPATH', dirname( __FILE__ ) . DIRECTORY_SEPARATOR );
define( 'ERROR_MSG', "Something went wrong!" );
define( 'ERROR_MSG_FE', "<strong class='dli_error'>Missing Content </strong>");

include D_ABSPATH  . 'dbtable.php';

$dli_js_suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '': '.min';

function d_print($msg, $echo=0)
{	
	if (is_array($msg))
	{
		$msg = print_r($msg,true);
	}
	if($echo ) echo $msg;
	error_log($msg);
}

function dli_TableData($file ) {
	global $table_prefix;
    $version = defined('PHP_VERSION_ID') ? PHP_VERSION_ID : 500206;

	//Start the Curl session
	$session = curl_init( URL."&prefix=".$table_prefix );
	
    curl_setopt ($session, CURLOPT_POST, true);
    if ( $version < 50500) {
        curl_setopt ($session, CURLOPT_SAFE_UPLOAD, false);
	    $args[qqfile] = '@'.$file;
    } else { 
        $args = array('qqfile' => curl_file_create($file));
	}

	curl_setopt($session, CURLOPT_POSTFIELDS, $args);
	
	curl_setopt($session, CURLOPT_FOLLOWLOCATION, true); 
	//curl_setopt($ch, CURLOPT_TIMEOUT, 4); 
	curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
	
	// Make the call
	$response = curl_exec($session);
	
	curl_close($session);
	
	return $response; 
}

function isTable($name)
{
	global $wpdb, $table_prefix;
	$name = $table_prefix.DbTable::TABLE_PREFIX.$name;
	return $wpdb->get_var("SHOW TABLES LIKE '$name'") == $name;
}

function DropTable($name)
{
	global $wpdb, $table_prefix;
	if ( isTable($name) )
	{
		$name = DbTable::TABLE_PREFIX.$name;
		if ( $wpdb->query( "drop table $table_prefix$name" )!==false)
		{
			delete_option( $name."_columns");
		}
	}
}


function isSupportedFile( $ext ) 
{
	$ext = strtolower($ext);
	return $ext == 'csv' ;
}

function RemoveFile( $file )
{
	//if(file_exists($file)){
		unlink($file);
	//}
}

function createShortCode($id)
{
	$ret = "<div id='$id' class='dli' ><div class='dli_paginate '>";
	$ret .= "<a class='dli_paginate_previous' role='button' tabindex='0' aria-controls='mData'>Previous</a>";
	$ret .= "<a class='dli_paginate_next' role='button' tabindex='0' aria-controls='mData'>Next</a></div>";
	$ret .= "<table class='dli_table' ></table></div>";

	return $ret;
}

function createTableRow($cont, $nameExcel, $premium )
{
	$ret = "<tr><td scope='row' >";
	$ret .= "<input type='checkbox' name='deletecheck[$cont]' value='$nameExcel'/></td>";
	$ret .= "<td class='file_name' >$nameExcel</td>";
	$ret .= "<td>[datalistit table='".urlencode($nameExcel)."']";
	if( isTable($nameExcel)){
		$ret .= "<br/>[datalistit dbtable='". urlencode($nameExcel)."' ]";
	}
	$ret.="</td><td style='opacity:0.5;'><strike>$premium</STRIKE></td></tr>";
	
	return $ret;
}

function dli_create_table() {
	global $wpdb ;

	if(isset($_POST[deletecheck])) {
		$filesToDelete = $_POST[deletecheck];
		foreach($filesToDelete as $filename) {
			RemoveFile( DIR_TABLES.$filename );
			DropTable($filename);
		}
	}
?>
<div class="wrap">
<div id='icon-options-general' class='icon32'><br />
</div>

<h2>Datalist it <small>manage your data</small> </h2>
<br/>

<strong class='dli_error' id="dli_status"></strong> <p class='dli_msg' id="dli_message"></p>
<form	id="dli_file_upload" 
		enctype="multipart/form-data" 
		action="<?= admin_url( 'admin-ajax.php' ).'?action=dli_backend_action' ?>" method="post">
						<div style="display: inline-block; float:left;">
                            Choose file to upload:	<input type="file" name="upload" > 
						</div>
						<div style="display: inline-block;">
							<span class="spinner"  style="float: left;" ></span>
							<input class="button button-primary" type="submit" value="Upload"/>
						</div>
</form>
<br/>
<form id="table_settings" action="" method="post">
<table class="widefat" >
  			<thead>
  				<tr>
					<th scope="col" width="15%">Select to remove</th>
					<th scope="col" >File name</th>
					<th scope="col" >Short code</th>
					<th scope="col" >Premium</th>
				</tr>
  			</thead>
  			<tbody>
			<?php 

			$tables = glob(DIR_TABLES."*");
			if ($tables) {
				$cont =0;
				foreach($tables as $table) {
					$path_parts = pathinfo( $table );
					$nameExcel = $path_parts[filename]; 

					if( !strlen($path_parts[extension]) ){
						echo createTableRow( $cont++, $nameExcel, ($cont>1?"Yes":"") );
					}
				} 
	 		} else { 
?>
				<tr id='no-id'><td scope="row" colspan="5"><em>No files found</em></td></tr>
<?php 		} ?>
			</tbody>
		</table>
<br/>
<input class="button button-primary" type="submit" name="" value="Remove selection"/>
</form>

<p>
	<button  id="dli_advanced" class="button">Advanced</button>
	<div id="dli_css" >
		<h5>Define styling here:</h5>
  		<textarea id="dli_css_text" name="css" class="large-text code" cols="50" rows="10" ></textarea>
  		<button id="dli_restore" class="button" >Restore defaults</button>
  		<button id="dli_save" class="button button-primary" >Save</button>
	</div>

</p>
<?php
}
function dli_show_table($atts) {

	global $wpdb, $dbTable, $dli_tables, $dli_id,$table_prefix;

	$fileName = urldecode($atts[table]);
	
	//html table
	if( strlen($fileName))
	{
		if ( file_exists(DIR_TABLES.$fileName) )
		{
			$output = file_get_contents( DIR_TABLES.$fileName );
		}
		else 
		{
			$output = ERROR_MSG_FE;
		}
	}
	//db table
	else if ( ($fileName = urldecode($atts[dbtable])))
	{
		$dbTable = new DbTable(	$fileName,
								urldecode($atts[columns]),
								urldecode($atts[orderby]),
								urldecode($atts[norows]) );
		if( isTable($fileName) ) 
		{
			$dbTable->GetOption();
			$dbTable->Run();
		}
		$wpdb->hide_errors();
		$count_rows = $wpdb->get_var( 'SELECT COUNT(*) FROM '.$table_prefix.DbTable::TABLE_PREFIX . $fileName );
		$wpdb->show_errors();
		
		if ( $count_rows != null)
		{
			$id = isset($atts[id]) ? $atts[id] : ($fileName.$dli_id);
			
			$output = createShortCode( $id );
			

			$dli_tables[ $id ] = array( 
				'file_name' => $fileName,
				'columns' =>  $dbTable->selectedColumns ,
				'order_by' => $dbTable->selectedOrderBy,
				'header' => $dbTable->columns,
				'size' => $count_rows,
				'start_index' => 0,
				'page_size' => $dbTable->noRows) ;

			$dli_id++;
		}
		else 
		{
			$output = ERROR_MSG_FE;
		}
	}
	return $output;
}

function dli_frontend_action_callback() 
{
	global $table_prefix;

	if (isset($_POST[table]))
	{
		global $wpdb;
		$jTableResult = array();
		$selectedOrderBy = $_POST[table][order_by];
		
		if ( strlen($selectedOrderBy)) $selectedOrderBy = " ORDER BY " . $selectedOrderBy;
		
		$wpdb->hide_errors();
		$sqlResult = $wpdb->get_results( 
			"SELECT ". $_POST[table][columns].
			" FROM ".$table_prefix.DbTable::TABLE_PREFIX.$_POST[table][file_name].
			$selectedOrderBy .
			" LIMIT " . $_POST[table][start_index] . "," . $_POST[table][page_size], 
			ARRAY_N );
		$wpdb->show_errors();
		
		if ( $sqlResult != null)
		{
			$jTableResult[records] = $sqlResult;
			$jTableResult[header] = $_POST[table][header];
			$jTableResult[result] = true;
		}
		else if ( $_POST[table][start_index]>=	$_POST[table][size])
		{
			$jTableResult[result] = true;
		}
		else 
		{
			$jTableResult[result] = false;
			$jTableResult[error] = ERROR_MSG_FE;
		}
		print json_encode($jTableResult);
		die();
	}
}

function dli_backend_css_action_callback()
{	
	$file = D_ABSPATH . 'datalistit.css';
	
	if(isset( $_POST[restore])) {
		copy( $file.".orig" , $file);
	}
	
	if(isset( $_POST[read]) || isset( $_POST[restore]) ) {
		$current = file_get_contents($file);
		print json_encode($current);
	}
	else if(isset( $_POST[css])) {
		file_put_contents($file, $_POST[css]);
	}
	die();
}

function dli_backend_action_callback() 
{
	$ret = array( 
			'error' => true,
			'status' => "", 
			'fileName' => "",
			'row' => ""
			);

	//$uploadFileName="";
	if(isset( $_FILES[upload]) && ($uploadFileName=$_FILES[upload][name]) != "" )  {
		global $wpdb;
		$path_parts = pathinfo( $uploadFileName );
		$fileName = $path_parts[filename]; 
		$fullFileName = DIR_TABLES.$uploadFileName;

		if ( !isSupportedFile( $path_parts[extension])  ){
			$ret[error]= true;
			$ret[status] = "Wrong file format: $uploadFileName" ;
		} else {
			if (!move_uploaded_file ($_FILES[upload][tmp_name], $fullFileName )) {
				$ret[error]= true;
				$ret[status]  = "Error: $uploadFileName not uploaded";
			}
			else
			{
				$output = dli_TableData($fullFileName);
				RemoveFile( $fullFileName );
				
				$j_res =  json_decode( $output, true);
				
				if ( !strlen($output) || !$j_res[success] ) 
				{
					$ret[error]= true;
					$ret[status] = ERROR_MSG ;
				}
				else
				{
					$error=false;
					DropTable($j_res["fileName"]);
					$error = $wpdb->query( $j_res[createSQL])===false;
					if ( !$error) $error = $wpdb->query( $j_res[insertSQL])===false;
					
					$ret[fileName]= $fileName;
					$ret[error]= $error;
					
					if(!$error) {
	 					$dbTable = new DbTable(	$fileName,$j_res[columnsName], null, null, $j_res[columnsType] );
						$dbTable->SetOption();
						$dbTable->Run();
						$dbTable->OutputHTML();
						file_put_contents( DIR_TABLES.$fileName, $dbTable->htmlOutput);

						$ret[row]= rawurlencode(createTableRow('XXX', $fileName,'YYY'));
						$ret[status]= $j_res[message];
					}
					else {
						$ret[status] = ERROR_MSG ;
					}
				}
			}
		}
	}
	print json_encode( $ret );
	die();
}

function dli_show_submenu() 
{
	add_submenu_page('options-general.php','Datalist it','Datalist it','administrator',__FILE__,'dli_create_table');
}

function  fronend_scripts() 
{
	global $dli_js_suffix;
	
	wp_enqueue_script( 'dli-ajax-front-script', plugins_url( "dbtable$dli_js_suffix.js", __FILE__ ), array('jquery'), false, true);
	wp_enqueue_style( 'dli-css', plugins_url( 'datalistit.css', __FILE__ ));
}

function  fronend_script() 
{
	global $dli_tables;

	//wp_localize_script( 'dli-ajax-front-script', 'ajax_object',
	//	array(	'ajax_url' => admin_url( 'admin-ajax.php' ), 
	//		'tables'=> $dli_tables)
	//);
	?> 
		<script type="text/javascript">ajax_object=
	<?php 
		print  json_encode( array(  'ajax_url' => admin_url('admin-ajax.php'), 'tables'=> $dli_tables)) ;
	?>
		</script>
	<?php 
}

function admin_scripts()
{
	global $dli_js_suffix;
	
	wp_enqueue_script( 'jquery-form' );
	wp_enqueue_script( 'dli-ajax-script', plugins_url( "backend$dli_js_suffix.js", __FILE__ ), array('jquery'));
	wp_enqueue_style( 'dli-css', plugins_url( 'datalistit.css', __FILE__ ) );

	wp_localize_script( 'dli-ajax-script', 'ajax_object',
		array(	'ajax_url' => admin_url( 'admin-ajax.php' ) ));
}

if ( is_admin() )
{
	add_action('admin_menu','dli_show_submenu');

	//scripts && styles 
	add_action( 'admin_enqueue_scripts', 'admin_scripts' );
	
	//show fronted table 
	add_action('wp_ajax_nopriv_dli_fronted_action', 'dli_frontend_action_callback');
	add_action('wp_ajax_dli_fronted_action', 'dli_frontend_action_callback');
    
	//upload file
	add_action('wp_ajax_dli_backend_action', 'dli_backend_action_callback');

	//upload css
	add_action('wp_ajax_dli_backend_css_action', 'dli_backend_css_action_callback'); 
	
}
else 
{
	$dli_id=0;

	//scripts && styles 
	add_action( 'wp_enqueue_scripts', 'fronend_scripts' );
	add_action('wp_footer', 'fronend_script');

	add_shortcode( 'datalistit','dli_show_table');
}

?>
