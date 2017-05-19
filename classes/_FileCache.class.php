<?php

class _FileCache {
	// Create a new FileCache instance.
	// Parameters:
	// $path: The path to the base directory which will be used for the cache.
	// $expirationTimeInSeconds: The expiration time, in seconds, for items added to the
	//     cache.  Any value less than 1 is automatically clamped to 1.
	//     Optional.  Defaults to 30.
	// $cleanInterval: How often to clean expired entries.  On average, expired entries will
	//     be cleaned every $cleanInterval get or set requests.  Any value < 1 will be clamped
	//     to 1.
	//     Optional.  Defaults to 1000.
	// $directoryDepth: The number of directories deep to make the cache.  The directory names
	//     are derived from segments of the sha1 hash of the cache key, working from left to right.
	//     Each segment consists of the next two hexadecimal characters of the sha1 hash of the
	//     cache key.  This must be between 1 and 10, inclusive.
	//     Optional.  Defaults to 2.
	public function __construct(
		$path = '',
		$expirationTimeInSeconds = 30,
		$cleanInterval = 2000,
		$directoryDepth = 2) {

		$this->path = '/tmp/_FileCache_/'.(string)$path;

		if ($expirationTimeInSeconds < 1) $expirationTimeInSeconds = 1;
		$this->expirationTimeInSeconds = (int)$expirationTimeInSeconds;

		if ($cleanInterval < 1) $cleanInterval = 1;
		$this->cleanInterval = (int)$cleanInterval;

		if ($directoryDepth<1) $directoryDepth=1; else if ($directoryDepth>10) $directoryDepth=10;
		$this->directoryDepth = (int)$directoryDepth;
	}

	// Get an object from the cache.
	// Parameters:
	//     $key: The cache key.
	// Returns:
	//     The object which was previously stored, or false if a cache miss occurred.
	public function get($key) {
//return false;
		if (($this->cleanInterval == 1) ||
			(rand(1, $this->cleanInterval) == $this->cleanInterval)) {
			$this->clean();
		}
		$val = false;
		$fn = $this->getCacheFilename($key);
		$exptime = time()-$this->expirationTimeInSeconds;
		$fileData = @file_get_contents($fn);
		if ($fileData!==false && @file_exists($fn) && (@filemtime($fn) > $exptime)) {
			$val = unserialize($fileData);
		}
		return $val;
	}

	// Store an object into the cache.
	// Parameters:
	//     $key: The cache key.
	//     $value: The object to store.
	public function set($key, $value) {
		if (($this->cleanInterval == 1) ||
			(rand(1, $this->cleanInterval) == $this->cleanInterval)) {
			$this->clean();
		}
		$fn = $this->getCacheFilename($key, true);
		$fileData = file_put_contents($fn,serialize($value));
	}

	// Delete an object from the cache.
	// Parameters:
	//     $key: The cache key.
	public function delete($key) {
		$fn = $this->getCacheFilename($key);

		// Delete the cache file.
		if (@unlink($fn)) {
			// Delete empty subdirectories, all the way up to but excluding the top-level cache dir.
			$refPath = rtrim($this->path, "/\\");
			$dir = rtrim(dirname($fn), "/\\");
			for ($i = 0; $i < $this->directoryDepth; $i++) {
				if (!@rmdir($dir)) break;
				$dir = rtrim(dirname($dir), "/\\");
			}
		}
	}

	// Clean expired entries.
	public function clean() {
		$this->cleanPath($this->path);
	}

	// Clean expired entries from a directory.
	public function cleanPath($path) {
		$exptime = time()-$this->expirationTimeInSeconds;
		foreach (@glob($path.'/*', GLOB_NOSORT) as $fn) {
			if (@is_dir($fn)) {
				$this->cleanPath($fn);
			} else if (@filemtime($fn) <= $exptime) {
				@unlink($fn);
			}
		}
		if ($path != $this->path) {
			@rmdir($path);
		}
	}

	private function getCacheFilename($key, $autoCreateDirectory = false) {
		$hash = sha1($key);
		$path = $this->path;
		for ($i = 0, $idx = 0; $i < $this->directoryDepth; $i++, $idx += 2) {
			$path .= '/'.substr($hash, $idx, 2);
		}
		if ($autoCreateDirectory) @mkdir($path, 0777, true);
		$path .= '/'.$hash;
		return $path;
	}

	public function fixPermissions($user) {
		$cmd = escapeshellcmd('chown -R '.$user.':'.$user. ' ' . $this->path);
		exec($cmd);
	}


}
