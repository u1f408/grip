<?php declare(strict_types=1);

namespace Grip\Renderer;
use Grip\Renderer\Renderer;

class RendererFactory {
	/** @var array<string, string> $renderers */
	private static $renderers = [
		"text/markdown" => "\\Grip\\Renderer\\MarkdownRenderer",
	];

	/**
	 * Return an instantiated Renderer class for the given MIME type.
	 */
	public static function create(string $mime_type): ?Renderer {
		$mime_type = strtolower(trim($mime_type));
		foreach (self::$renderers as $r_mime => $r_klass) {
			if ($r_mime === $mime_type) {
				return new $r_klass;
			}
		}

		return null;
	}
}
