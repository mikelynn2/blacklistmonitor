<?php
$apiKey = 'API-KEY-GOES-HERE';

$requestBody =
	"apiKey=".urlencode($apiKey).
	"&type=blacklistStatus".
	"&data=all";

$return = httpPost('url/api.php', $requestBody);
$return = json_decode($return, true);
$results = $return['result'];

$body = "";
$body .= "<table border='1'>";
$body .= "<tr>";
$body .= "<th>host</th>";
$body .= "<th>status</th>";
$body .= "</tr>";

foreach($results as $result){
	$body .= "<tr>";
	$body .= "<td>".htmlentities($result['host'])."</td>";
	$body .= "<td nowrap>";
	if($result['isBlocked']==0){
		$body .= "OK";
	}else{
		foreach($result['status'] as $r){
			if($r[1] == false || $r[1] == ''){

			}else{
				$body .= htmlentities($r[0]) . " - " . htmlentities($r[1])."<br>";
			}
		}
	}
	$body .= "</td>";
	$body .= "</tr>";
}

$body .= "</table>";

echo $body;


function httpPost($url, $vars){
	//open connection
	$ch = curl_init();

	//set the url, number of POST vars, POST data
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_POST,true);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch,CURLOPT_FAILONERROR,true);
	curl_setopt($ch,CURLOPT_POSTFIELDS,$vars);
	//execute post
	return 	curl_exec($ch);
}