<?php
class_exists('Setup', false) or include('Setup.class.php');

require_once('TwitterAPIExchange.php');

class Twitter {

	public $settings = array();

	public $twitter = null;

	public function __construct(){
			$this->settings = array(
			'oauth_access_token' => Setup::$settings['twitter_oauth_access_token'],
			'oauth_access_token_secret' => Setup::$settings['twitter_access_token_secret'],
			'consumer_key' => Setup::$settings['twitter_consumer_key'],
			'consumer_secret' => Setup::$settings['twitter_consumer_secret']
		);
		$this->twitter = new TwitterAPIExchange($this->settings);
	}

	public function follow($user){
		$postfields = array(
			'screen_name'=>$user,
			'follow'=>'true'
		);
		return $this->twitter->buildOauth('https://api.twitter.com/1.1/friendships/create.json', 'POST')
			->setPostfields($postfields)
			->performRequest();
	}

	public function message($user, $message){
		$message = trim($message);
		$postfields = array(
			'screen_name'=>$user,
			'text'=>$message
		);
		return $this->twitter->buildOauth('https://api.twitter.com/1.1/direct_messages/new.json', 'POST')
			->setPostfields($postfields)
			->performRequest();
	}

}