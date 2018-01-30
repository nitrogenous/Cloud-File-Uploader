<?php 
require_once(__DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."XUP".DIRECTORY_SEPARATOR."main.php");
require_once(__DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."XUP".DIRECTORY_SEPARATOR."adapters".DIRECTORY_SEPARATOR."drive.php");
require_once(__DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."XUP".DIRECTORY_SEPARATOR."adapters".DIRECTORY_SEPARATOR."dropbox.php");
require_once(__DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."XUP".DIRECTORY_SEPARATOR."adapters".DIRECTORY_SEPARATOR."amazonwebservices.php");

use XUP\Uploader\Main;
use XUP\Uploader\Drive;
use XUP\Uploader\Dropbox;
use XUP\Uploader\AmazonWebServices;
$output = array();
$services = $_POST["clouds"];
$services = explode(",",$services);
$services = array_filter($services);
$action = $_POST["action"];	
foreach ($services as $service) {
	$class = "XUP\Uploader\\".$service;
	$adapter = new $class();
	$output[$service] = $action($adapter,$_POST);
}
exit(json_encode($output));
function select($adapter,$post) {
	$qid = injection($post["qid"]);
	$formid = injection($post["formid"]);
	return $adapter->select($formid,$qid);
}

function insert($adapter,$post) {
	$qid = injection($post["qid"]);
	$key = $post["key"];	
	$formid = injection($post["formid"]);
	return $adapter->insert($formid,$qid,$key);			
	// }
}
function deleteKey($adapter,$params){
	return null;
}
function injection($str) {
	$bad = array(
		'<!--', '-->',
		"'", '"',
		'<', '>',
		'&', '$',
		'=',
		';',
		'?',
		'/',
		'!',
		'#',
		'%20',		//space
		'%22',		// "
		'%3c',		// <
		'%253c',	// <
		'%3e',		// >
		'%0e',		// >
		'%28',		// (
		'%29',		// )
		'%2528',	// (
		'%26',		// &
		'%24',		// $
		'%3f',		// ?
		'%3b',		// ;
		'%3d',		// =
		'%2F',		// /
		'%2E',		// .
		// '46', 	// .
		// '47'		// /
	);
	do
	{
		$old = $str;
		$str = str_replace($bad, ' ', $str);
	}
	while ($old !== $str);
	return $str;	
}

