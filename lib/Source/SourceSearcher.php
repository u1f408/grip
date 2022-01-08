<?php declare(strict_types=1);

namespace Grip\Source;
use Grip\Configuration;
use Grip\Source\Source;
use Grip\PageEntry;

final class SourceSearcher {
	public static function getEntry(string $slug): ?PageEntry {
		foreach (Configuration::$sources as $sname => $source) {
			if ($source->hasEntry($slug)) {
				return $source->getEntry($slug);
			}
		}

		return null;
	}
}
