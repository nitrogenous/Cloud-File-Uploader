<?php
require_once(DIRECTORY_SEPARATOR."www".DIRECTORY_SEPARATOR."v3".DIRECTORY_SEPARATOR."toprak".DIRECTORY_SEPARATOR."Adapter". DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php");
// require_once '/www/v3/toprak/lib/init.php';

use Aws\S3\S3Client;
use Aws\Common\Credentials\Credentials;

$worker = new GearmanWorker();
$worker->addServer("127.0.0.1", "4730");
$worker->addFunction("toprakAWS", "toprakAwsUpload");
$worker->addFunction("toprakAWSRemove", "toprakAwsRemove");
while ($worker->work());

function toprakAwsUpload($job) {
	try{
		$params = (array)json_decode($job->workload());
		$formid = $params["formid"];
		$folder = $params["folder"];
		$qid = $params["qid"];
		$file = $params["file"];
		$keys = (array)json_decode($params["key"]);
		var_dump($params);
		var_dump($keys);
		if(empty($keys)){
			return json_encode(array("Error" => "Key Does Not Exist","File" => null,'Folder' => null,"Url" => null, "Remove" => null));		
		}
		var_dump($params);
		$access = $keys["access"];
		$secret = $keys["secret"];
		$bucket = $keys["bucket"];
		$region = $keys["region"];

		$base_path = DIRECTORY_SEPARATOR . "tmp";
		$path =  $folder . DIRECTORY_SEPARATOR. "questionid".$qid;
		$key = "toprak" . DIRECTORY_SEPARATOR . $formid . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR. "questionid".$qid. DIRECTORY_SEPARATOR . $file;
		$sourcepath =$base_path.DIRECTORY_SEPARATOR . $formid . DIRECTORY_SEPARATOR .$path.DIRECTORY_SEPARATOR.$file;

		if(!file_exists($sourcepath)){
			return json_encode(array("Error" => "File Does Not Exist","File" => null,'Folder' => null,"Url" => null, "Remove" => null));
		}
		else{
			$s3 = S3Client::factory(array(
				"region" => $region,
				'version' => '2006-03-01',
				"credentials" => array(
					"key" => $access,
					"secret" => $secret
				)
			));
			$result = $s3->putObject(array(
				"Bucket" => $bucket,
				"Key" => $key,
				"SourceFile" => $sourcepath,
				"ContenType" => "application/octet-stream",
				"ACL" => "public-read"
			));
			$url = $result["ObjectURL"];
			var_dump($url);
			return json_encode(array("Error" => 0,"File" => $file,'Folder' => null,"Url" => $url, "Remove" => $key));
		}
	}
	catch(Exception $e){
		return json_encode(array("Error" => $e,"File" => null,'Folder' => null,"Url" => null, "Remove" => null));
	}
	
}
function toprakAwsRemove($job){
		try{
			$params = (array)json_decode($job->workload());
			var_dump($params);
			$keys = (array)json_decode($params["key"]);
			$access = $keys["access"];
			$secret = $keys["secret"];
			$bucket = $keys["bucket"];
			$region = $keys["region"];
			$remove = (array)json_decode($params["remove"]);
			$remove = $remove["Amazon"];

			$s3 = S3Client::factory(array(
				"region" => $region,
				'version' => '2006-03-01',
				"credentials" => array(
					"key" => $access,
					"secret" => $secret
				)
			));
			$result = $s3->deleteObject(array(
				"Bucket" => $bucket,
				"Key" => $remove
			));
			var_dump($result);
		}
		catch(Exception $e){
			return $e;
		}
	}