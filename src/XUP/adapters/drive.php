<?php
namespace XUP\Uploader;

class Drive extends XUP {
	protected	$value;
	protected 	$key;
	protected	$status;
	function __construct() {	
		$this->value = strtolower((new \ReflectionClass($this))->getShortName());
	}
	public function test() {
		return $this->value . ":✔";
	}
	public function select($formid,$qid) {
		if(empty($formid) || empty($qid) || empty($this->value)) {
			return "Error1";
		}
		$sql = "SELECT `key` FROM `widget_access_keys` WHERE formId = ".addslashes($formid)." AND questionId = ".addslashes($qid)." AND value = '".$this->value."'";
		$result = $this->query($sql);
		if ($result->num_rows > 0) {
			while($row = $result->fetch_assoc()){
				$this->key = $row['key'];
				return $row['key'];
			}
		}
		else{
			return null;
		};
	}
	public function insert($formid,$qid,$key) {
		if(empty($formid) || empty($qid) || empty($key) || empty($this->value)) {
			return "Error";
		}
		if($this->select(addslashes($formid),addslashes($qid)) == null){
			$key = $this->tokens($formid,$qid,$key);
		}
		$sql = "REPLACE INTO widget_access_keys (`formId`,`questionId`,`value`,`key`) VALUES (".addslashes($formid).",".addslashes($qid).",'".addslashes($this->value)."','".$key."')";
		$result = $this->query($sql);
		if ($result == true) {
			$this->value = true;
			return true;
		}
		else{
			return false;
		}
	}
	public function upload($params) {
		$params = (array)json_decode($params);
		$job = json_encode(array("formid" => $params["formid"],"folder"=> $params["folder"],"qid" =>  $params["qid"], "key" => $this->select($params["formid"],$params["qid"]), "file" =>  $params["file"], "folderKey" => $params["folderKey"]));
		$client = new \GearmanClient();
		$client->addServer("127.0.0.1","4730");	
		return $client->doNormal("toprakDrive",$job);
	}
	public function deleteKey($params){
		return null;
	}
	public function deleteFile($params) {
		$params = (array)json_decode($params);
		$job = json_encode(array("key" => $this->select($params["formid"],$params["qid"]),"remove" => $params["remove"]));
		$client = new \GearmanClient();
		$client->addServer("127.0.0.1","4730");
		return $client->doBackground("toprakDriveRemove",$job);
	}
	/**
		Drive api doesnt gives directly access token and refresh token so this function getting them with auth key
	*/
	public function tokens($formid,$qid,$auth) {
		require_once '/www/v3/toprak/Adapter/vendor/autoload.php';
		$code = explode('"',$auth);
		$del = array('"',"{","}","code",":");
		do{
			$old = $code;
			$code = str_replace($del,"",$code);
			$code = array_filter($code);
		}
		while($old !== $code);
		$code = implode($code);
		$client = new \Google_Client();
		$client->setAuthConfig("client_secrets.json");
		$client->addScope(\Google_Service_Drive::DRIVE_METADATA_READONLY); 
		$client->setRedirectUri("https://toprak.jotform.pro"); 
		$client->setAccessType("offline");
		$client->setApprovalPrompt("force");
		$client->setIncludeGrantedScopes(true);
		$client->authenticate($code);
		$resp = $client->getAccessToken($code);
		$tokens = json_encode(array("access_token" => $resp["access_token"],"refresh_token" => $resp["refresh_token"]));
		return $tokens;
	}
	public function query($query){
		$con = mysqli_connect("127.0.0.1","toprak","toprak","toprak_jotform3");
		$result = mysqli_query($con,$query);
		mysqli_close($con);
		return $result;
	}
}