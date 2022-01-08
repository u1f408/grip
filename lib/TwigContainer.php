<?php declare(strict_types=1);

namespace Grip;
use \Twig\Loader\FilesystemLoader;
use \Twig\Environment;

class TwigContainer {
	/** @var \Twig\Environment $twig */
	private static $twig;

	public static function get(): Environment {
		if (self::$twig == null) {
			$loader = new FilesystemLoader(array_filter([
				is_dir($site_tpl = SITE_PATH . '/templates') ? $site_tpl : null,
				BASE_PATH . '/templates',
			]));
			$loader->addPath(BASE_PATH . '/templates', 'base');

			self::$twig = new Environment($loader, [
				'cache' => SITE_PATH . '/cache/twig',
			]);
		}

		return self::$twig;
	}
}
