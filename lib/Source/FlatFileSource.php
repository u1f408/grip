<?php declare(strict_types=1);

namespace Grip\Source;
use Grip\Source\Source;
use Grip\PageEntry;

class FlatFileSource extends Source {
	/** @var array<string, string> EXTENSION_MIME_TYPES */
	const EXTENSION_MIME_TYPES = [
		'.css' => 'text/css',
		'.htm' => 'text/html',
		'.html' => 'text/html',
		'.md' => 'text/markdown',
		'.mkd' => 'text/markdown',
		'.markdown' => 'text/markdown'
	];

	private function getFilePathFromSlug(string $slug): ?string {
		// Get the real source directory path
		$sourcepath = realpath(SITE_PATH . '/' . $this->config['path']);
		if ($sourcepath === false) $sourcepath = $this->config['path'];

		// Get the base file path (without extension)
		$basepath = $sourcepath . $slug;
		if (is_dir($basepath)) $basepath = $basepath . '/index';

		// If the base path exists, return that directly
		if (file_exists($basepath)) {
			return ($p = realpath($basepath)) !== false ? $p : $basepath;
		}

		// Do the search
		foreach (array_keys(self::EXTENSION_MIME_TYPES) as $ext) {
			$filepath = $basepath . $ext;
			if (file_exists($filepath)) {
				return ($p = realpath($filepath)) !== false ? $p : $filepath;
			}
		}

		return null;
	}

	public function hasEntry(string $slug): bool {
		return $this->getFilePathFromSlug($slug) !== null;
	}

	public function getEntry(string $slug): ?PageEntry {
		$filepath = $this->getFilePathFromSlug($slug);
		if ($filepath === null) return null;
		$raw_content = file_get_contents($filepath);
		if ($raw_content === false) return null;

		// Parse out the file metadata
		$split_content = true;
		$metadata = yaml_parse($raw_content, 0);
		if ($metadata === false) {
			$split_content = false;
			$metadata = [];
		}

		// Split raw_content to get the file content after the metadata
		$content = $raw_content;
		if ($split_content) {
			[$_, $__, $content] = explode("---\n", $content, 3);
			$content = trim($content);
		}

		// Return created PageEntry
		return new PageEntry(
			$this,
			$slug,
			self::EXTENSION_MIME_TYPES['.' . pathinfo($filepath, PATHINFO_EXTENSION)],
			$content,
			$metadata,
		);
	}
}
