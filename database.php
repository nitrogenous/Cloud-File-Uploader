<?php require_once(__DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."XUP".DIRECTORY_SEPARATOR."main.php");require_once(__DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."XUP".DIRECTORY_SEPARATOR."adapters".DIRECTORY_SEPARATOR."drive.php");require_once(__DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."XUP".DIRECTORY_SEPARATOR."adapters".DIRECTORY_SEPARATOR."dropbox.php");use XUP\Uploader\Main;use XUP\Uploader\Drive;use XUP\Uploader\Dropbox;$action = $_POST["action"];$values = array_filter(explode(",",$_POST["clouds"]));	$DB = new Database();foreach ($values as $key) {	$result = $DB->$action($_POST,$key);	$DB->send_status($key,$result);}class Database{	protected $bad = array(			'<!--', '-->',			"'", '"',			'<', '>',			'&', '$',			'=',			';',			'?',			'/',			'!',			'#',			'%20',		//space			'%22',		// "			'%3c',		// <			'%253c',	// <			'%3e',		// >			'%0e',		// >			'%28',		// (			'%29',		// )			'%2528',	// (			'%26',		// &			'%24',		// $			'%3f',		// ?			'%3b',		// ;			'%3d',		// =			'%2F',		// /			'%2E',		// .			// '46', 		// .			// '47'		// /		);	protected $access_token;	function check($post,$value) {		$qid = $this->injection($post["qid"]);		$formid = $this->injection($post["formid"]);		$value =  "XUP\Uploader\\".$value;		$adapter = new $value();		return $adapter->check($formid,$qid);	}	function save($post,$value) {		$qid = $this->injection($post["qid"]);		$key = $post["key"];		$formid = $this->injection($post["formid"]);		$value =  "XUP\Uploader\\".$value;		$adapter = new $value();		return $adapter->save($formid,$qid,$key);	}	function upload($post,$value) {		$formid = $this->injection($post["formid"]);		$folder = $this->injection($post["folder"]);		$qid = $this->injection($post["qid"]);		$file = $this->injection($post["file"]);		var_dump($formid,$folder,$qid,$file);		$value =  "XUP\Uploader\\".$value;		$adapter = new $value();		return $adapter->upload($formid,$folder,$qid,$file);	}	function injection($str) {		do		{			$old = $str;			$str = str_replace($this->bad, ' ', $str);		// if(stripos($str, '4647'))		// {		// 	$str = str_replace('4647', '', $str);		// }		}		while ($old !== $str);		return $str;		}	function send_status($first,$second) {		exit(json_encode(array($first => $second)));		}}