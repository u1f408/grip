<?php declare(strict_types=1);

namespace Grip;
use Dotenv\Dotenv;

class Configuration {
	/** @var string SOURCE_NAME_MATCH */
	const SOURCE_NAME_MATCH = '/^SOURCE_([A-Z0-9]{2,})_CLASS$/';

	/** @var array<string, string> $raw_config */
	public static $raw_config;

	/** @var array<string, string> $site */
	public static $site;

	/** @var array<string, \Grip\Source\Source> $sources */
	public static $sources;

	/**
	 * Load the configuration file from our site directory.
	 * 
	 * By default, the site directory is the `site/` subdirectory of
	 * `BASE_PATH`, but this can be overridden by defining a `SITE_PATH`
	 * environment variable in the application environment, or by a PHP
	 * define before this function is called (`define("SITE_PATH", ...)`).
	 */
	public static function loadBase(): void {
		$site_dir = defined("SITE_PATH") ? SITE_PATH : BASE_PATH . '/site';

		// Use dotenv to load potential site configuration
		$environ = Dotenv::createArrayBacked($site_dir, 'config')->safeLoad();

		// Update `$site_dir` if there is a defined SITE_DIR in the freshly
		// loaded potential site configuration (allowing indirection)
		if (array_key_exists("SITE_PATH", $environ)) {
			$site_dir = $environ["SITE_PATH"];
		}

		// Update `$site_dir` if there is a defined SITE_DIR in the real
		// environment
		if (array_key_exists("SITE_PATH", $_ENV)) {
			$site_dir = $_ENV["SITE_PATH"];
		}

		// Define SITE_DIR (potential redefine) with the realpath of our
		// actual site directory
		define("SITE_PATH", ($p = realpath($site_dir)) === false ? $site_dir : $p);

		// Use dotenv to load the actual site configuration
		$real_environ = Dotenv::createArrayBacked(SITE_PATH, 'config')->load();

		// Store that on `self`
		self::$raw_config = $real_environ;
	}

	/**
	 * Load the site configuration.
	 */
	public static function load() {
		if (self::$raw_config === null) {
			self::loadBase();
		}

		self::$site = self::parseSite();
		self::$sources = self::parseSources();
	}

	/**
	 * Parse site variables from the site config.
	 */
	private static function parseSite(): array {
		$site = [];

		foreach (self::$raw_config as $key => $value) {
			if (!str_starts_with($key, "SITEVAR_")) continue;

			$normkey = self::normalizeKey($key, "SITEVAR_");
			$site[$normkey] = $value;
		}

		return $site;
	}

	/**
	 * Parse out individual source configurations from the site config,
	 * and instantiate the source classes.
	 */
	private static function parseSources(): array {
		$sources = [];

		// Get source names
		$source_names = [];
		foreach (self::$raw_config as $key => $value) {
			if (preg_match(self::SOURCE_NAME_MATCH, $key) === 1) {
				$sname = preg_replace(self::SOURCE_NAME_MATCH, '${1}', $key);
				$source_names[] = $sname;
			}
		}

		// Parse out source configurations and instantiate
		foreach ($source_names as $sname) {
			$sconf = [];
			foreach (self::$raw_config as $key => $value) {
				if (strpos($key, "SOURCE_{$sname}_") !== 0) continue;
				$normkey = self::normalizeKey($key, "SOURCE_{$sname}_");
				$sconf[$normkey] = $value;
			}

			$sclass = array_key_exists("class", $sconf) ? $sconf["class"] : null;
			if ($sclass !== null && class_exists($sclass)) {
				$inst = new $sclass ($sname, $sconf);
				$sources[strtolower($sname)] = $inst;
			}
		}

		return $sources;
	}

	/**
	 * Normalize a configuration key name, optionally stripping a prefix
	 * from the key name before normalization.
	 */
	private static function normalizeKey(string $key, string $prefix = ""): string {
		$newkey = trim($key);

		// Trim prefix
		if (strpos($newkey, $prefix) === 0) {
			$newkey = substr($newkey, strlen($prefix));
		}

		$newkey = strtolower($newkey);

		return $newkey;
	}
}
