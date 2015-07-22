<?php
class_exists('_Logging', false) or include('_Logging.class.php');

class Setup {

	public static $instance = null;
	public static $connectionArray = array();
	public static $settings = array();

	public function __construct() {

		if(self::$instance===null){

			if(!file_exists('/etc/blacklistmonitor.cfg')) echo('no config file in /etc/blacklistmonitor.cfg');
			ini_set('error_reporting', E_ALL | E_STRICT | E_NOTICE);
			$cfg = parse_ini_file('/etc/blacklistmonitor.cfg', false);
			ini_set('display_errors', $cfg['display_errors']);
			ini_set('error_log', $cfg['log_path']);

			self::$settings = $cfg;
			self::$settings['dns_servers'] = explode(',',$cfg['dns_servers']);

			// clean up
			if(Setup::$settings['rbl_txt_extended_status']=='true' ||
			  Setup::$settings['rbl_txt_extended_status']=='1'){
				Setup::$settings['rbl_txt_extended_status'] = true;
			}else{
				Setup::$settings['rbl_txt_extended_status'] = false;
			}

			self::$connectionArray = array(
				$cfg['db_host'],
				$cfg['db_username'],
				$cfg['db_password'],
				$cfg['db_database']
			);

			_Logging::$logFileLocation = $cfg['log_path'];

		}

	}

}

Setup::$instance = new Setup();
if(php_sapi_name()!=='cli'){
	session_start();
}