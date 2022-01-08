<?php declare(strict_types=1);

namespace Grip\Source;
use Grip\PageEntry;

abstract class Source {
	/** @var string $name */
	public $name;

	/** @var array<string, string> $config */
	public $config;

	public function __construct(string $name, array $config) {
		$this->name = $name;
		$this->config = $config;
	}

	abstract public function hasEntry(string $slug): bool;
	abstract public function getEntry(string $slug): ?PageEntry;
}
