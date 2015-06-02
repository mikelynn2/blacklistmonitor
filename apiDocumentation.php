<?php
class_exists('Setup', false) or include('classes/Setup.class.php');
class_exists('Utilities', false) or include('classes/Utilities.class.php');

if(Utilities::isLoggedIn()===false){
	header('Location: login.php');
	exit();
}

$titlePreFix = "api documentation";
include('header.inc.php');
include('accountSubnav.inc.php');

$user = Utilities::getAccount();

?>
<h4>API Documentation</h4>
<p>
The API is a JSON HTTP POST based API.
All responses from the API are JSON.
</p>

<h4>POST URL: <?php echo Setup::$settings['base_url']; ?>/api.php</h4>
<table border="1">
	<tr>
		<th>Post Variable:</th>
		<th></th>
	</tr>
	<tr>
		<td>apiKey</td>
		<td>Your api key: <?php echo $user['apiKey'];?></td>
	</tr>
	<tr>
		<td>type</td>
		<td>
		updateDomains - pass data var of whitespace delimited domains.  Whitespace can be any tabs, spaces, new lines.<br/><br/>
		updateIPs - pass data var of whitespace delimited ip addresses/ranges.  Whitespace can be any tabs, spaces, new lines.<br/><br/>
		checkHostStatus - pass data var of a single ip (not a range) or domain name for current black list status.<br/><br/>
		blacklistStatus - data var - all (default) | blocked | changed | clean. Returns blacklist status of all current ips and domains<br/><br/>
		</td>
	</tr>
	<tr>
		<td>data</td>
		<td>When calling functions that need data passed use the var data.</td>
	</tr>
</table>

<br/>
<h4>Response</h4>
<p>
Below is an example response from the API in JSON.  It will always include a status.  Either success or failure and if a result is required for the call it will be included as well as with the 
blacklistStatus call.
</p>

<pre>
{&quot;status&quot;:&quot;success&quot;,&quot;result&quot;:&quot;&quot;}
</pre>

<h4>PHP Example - Pulling all hosts current status</h4>
<br/>
<pre>
&lt;?php
$apiKey = '<?php echo $user['apiKey'];?>';

$requestBody =
	&quot;apiKey=&quot;.urlencode($apiKey).
	&quot;&amp;type=blacklistStatus&quot;.
	&quot;&amp;data=all&quot;;

$return = httpPost('<?php echo Setup::$settings['base_url']; ?>/api.php', $requestBody);
$return = json_decode($return, true);
$results = $return['result'];

$body = &quot;&quot;;
$body .= &quot;&lt;table border='1'&gt;&quot;;
$body .= &quot;&lt;tr&gt;&quot;;
$body .= &quot;&lt;th&gt;host&lt;/th&gt;&quot;;
$body .= &quot;&lt;th&gt;status&lt;/th&gt;&quot;;
$body .= &quot;&lt;/tr&gt;&quot;;

foreach($results as $result){
	$body .= &quot;&lt;tr&gt;&quot;;
	$body .= &quot;&lt;td&gt;&quot;.htmlentities($result['host']).&quot;&lt;/td&gt;&quot;;
	$body .= &quot;&lt;td nowrap&gt;&quot;;
	if($result['isBlocked']==0){
		$body .= &quot;OK&quot;;
	}else{
		foreach($result['status'] as $r){
			if($r[1] == false || $r[1] == ''){

			}else{
				$body .= htmlentities($r[0]) . &quot; - &quot; . htmlentities($r[1]).&quot;&lt;br&gt;&quot;;
			}
		}
	}
	$body .= &quot;&lt;/td&gt;&quot;;
	$body .= &quot;&lt;/tr&gt;&quot;;
}

$body .= &quot;&lt;/table&gt;&quot;;

echo $body;

function httpPost($url, $vars){
	//open connection
	$ch = curl_init();

	//set the url, number of POST vars, POST data
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_POST,true);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch,CURLOPT_FAILONERROR,true);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
	curl_setopt($ch,CURLOPT_POSTFIELDS,$vars);
	//execute post
	return 	curl_exec($ch);
}
</pre>

<br/><br/><br/>


<a name="callBack"></a>

<h4>Callback API</h4>

<p>
A JSON array will be posted for each host upon a status change with that host.  Each host will be called back in seperate requests.  You set the call back URL on your <a href="account.php">profile</a> page.
</p>

<h4>Example Posted JSON</h4>
<pre>
{
	"host":"samplehosttest.com",
	"isBlocked":true,
	"rDNS":"reverse-dns-sample.samplehosttest.com",
	"blocks":[
		["l2.apews.org","Listed at APEWS-L2 - visit http:\/\/www.apews.org\/?page=test&amp;C=131&amp;E=1402188&amp;ip=127.0.0.1"],
		["b.barracudacentral.org","127.0.0.2"]
	]
}
</pre>

<br/><br/><br/>
<br/><br/><br/>

<?php
include('footer.inc.php');
?>
