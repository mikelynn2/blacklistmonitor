<?php
class_exists('Utilities', false) or include('Utilities.class.php');

class _IpAddresses {

	public static function isIPAddress($s) {
		if (preg_match(
			'/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/',
			$s) != 0) {
			return true;
		}
		return false;
	}

	public static function isCidrIPAddress($s) {
		if (preg_match(
			'/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])(\/(\d|[1-2]\d|3[0-2]))$/',
			$s) != 0) {
			return true;
		}
		return false;
	}

	// returns an array of a cidr like 127.0.0.1/24 - the array will be 127.0.0.1 in [0] and 127.0.0.254 in [1]
	public static function cidrToRange($cidr) {
		$range = array();
		$cidr = explode('/', $cidr);
		if(!isset($cidr[1])) return false;
		$x = ip2long($cidr[1]);
		$nm = long2ip($x) == $cidr[1] ? $x : (0xffffffff << (32 - $cidr[1]));
		$ip = ip2long($cidr[0]);
		$nw = ($ip & $nm);
	   	$bc = $nw | (~$nm);
//		$range[0]=long2ip($nw+1);
//		$range[1]=long2ip($bc-1);
		$range[0]=long2ip($nw);
		$range[1]=long2ip($bc);
		return $range;
	}

	public static function isPrivateIP($ip) {
		if (!_IpAddresses::isIPAddress($ip)) return false;
		$slash8 = _IpAddresses::checkIpToNetwork($ip, '255.0.0.0');
		if ( ($slash8 == '10.0.0.0') || ($slash8 == '127.0.0.0') ) return true;
		$slash12 = _IpAddresses::checkIpToNetwork($ip, '255.240.0.0');
		if ($slash12 == '172.16.0.0') return true;
		$slash16 = _IpAddresses::checkIpToNetwork($ip, '255.255.0.0');
		if ( ($slash16 == '169.254.0.0') || ($slash16 == '192.168.0.0') ) return true;
		return false;
	}

	public static function getHostByIp($ip) {
		$ip = escapeshellarg($ip);
		$output = `host -W 1 $ip`;
//		if (ereg('.*pointer ([A-Za-z0-9.-]+)\..*',$output,$regs)) return $regs[1];
//		echo $output;
		$ip = Utilities::parseBetweenText($output, 'pointer', "\n", false, false, true);
		$ip = trim($ip,'. ');
		if($ip==''){
			return false;
  		}else{
			return $ip;
		}
	}

	public static function checkIpToNetwork($ip, $network) {
		// The $ip parameter must be a valid, parseable IP address.
		if (($ipLong = ip2long($ip)) === false) {
			return false;
		}
		// Break the network address into two pieces, separated by the slash.
		// there must be exactly two pieces, or it is invalid.
		$networkPieces = explode('/', $network);
		if (count($networkPieces) != 2) {
			return false;
		}
		// Parse the IP address portion of the network address.
		// If unparseable, it is invalide.
		if (($networkLong = ip2long($networkPieces[0])) === false) {
			return false;
		}
		// Count the dots in the netmask portion.  It must be either three (if it's a netmask)
		// or zero (if it's a most-significant-bits count).  Parse or calculate the netmask
		// accordingly.
		$counts = count_chars($networkPieces[1], 1);
		$idx = ord('.');
		$dotCount = isset($counts[$idx]) ? $counts[$idx] : 0;
		if ($dotCount == 3) {
			if (($mask = ip2long($networkPieces[1])) === false) {
				return false;
			}
		} else if ($dotCount == 0) {
			$bitCount = (int)$networkPieces[1];
			if ( ($bitCount < 0) || ($bitCount > 32) ) {
				// Invalid bit count.
				return false;
			} else if ($bitCount == 0) {
				// << 32 in PHP doesn't seem to do anything; so we work around it here.
				$mask = 0;
			} else if ($bitCount == 32) {
				// Prevent << 0, which effects no change.
				$mask = 0xffffffff;
			} else {
				$mask = (0xffffffff << (32 - $bitCount)) & 0xffffffff;
			}
		} else {
			// Invalid dot count in mask.
			return false;
		}
		// In order for the IP address to be in the network, the biwise-and of the IP address
		// with the netmask, must equal the bitwise-and of the network address with the netmask.
		return ($ipLong & $mask) == ($networkLong & $mask);
	}

	public static function getRemoteIP() {
		return _IpAddresses::getRemoteHardIP();
	}

	public static function getAllIPsFromString($str, $dedup = true) {
		// Find ips in a string and return array of them
		$r = "/(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)/";
		$matches = array();
		preg_match_all($r, $str, $matches);
		$matches = (isset($matches[0]) ? $matches[0] : array());
		return ($dedup ? array_unique($matches) : $matches);
	}

}