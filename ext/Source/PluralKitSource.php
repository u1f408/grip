<?php declare(strict_types=1);

namespace GripExt\Source;
use IrisHelpers\CurlHelpers;
use Michelf\Markdown;
use Grip\Source\Source;
use Grip\TwigContainer;
use Grip\PageEntry;

class PluralKitSource extends Source {
	/** @var string PK_API_BASE */
	const PK_API_BASE = "https://api.pluralkit.me/v2";

	private function renderDescription(?string $description): ?string {
		if ($description === null) return null;
		if (($pos = strpos($description, $this->config['description_delim'])) === false) {
			return null;
		}

		$description = trim(substr($description, 0, $pos));
		$description = Markdown::defaultTransform($description);
		return trim($description);
	}

	private function fetchSystem(): array {
		$system = [];

		// Get core system details
		$pk_system = CurlHelpers::fetchUrl(self::PK_API_BASE . "/systems/{$this->config['system']}", [], true);
		if ($pk_system === false) return [];
		$system['id'] = $pk_system['id'];
		$system['name'] = $pk_system['name'];
		$system['color'] = $pk_system['color'];
		$system['avatar_url'] = $pk_system['avatar_url'];

		// Get current fronters
		$system['fronters'] = [];
		$pk_fronters = CurlHelpers::fetchUrl(self::PK_API_BASE . "/systems/{$this->config['system']}/fronters", [], true);
		if ($pk_fronters !== false) {
			foreach ($pk_fronters['members'] as $member) {
				$system['fronters'][] = $member['id'];
			}
		}

		// Get system members
		$system['members'] = [];
		$pk_members = CurlHelpers::fetchUrl(self::PK_API_BASE . "/systems/{$this->config['system']}/members", [], true);
		if ($pk_members !== false) {
			foreach ($pk_members as $member) {
				$description = $this->renderDescription($member['description']);
				if ($description === null) continue;

				$system['members'][$member['id']] = [
					'name' => $member['name'],
					'color' => $member['color'],
					'birthday' => $member['birthday'],
					'pronouns' => $member['pronouns'],
					'avatar_url' => $member['avatar_url'],
					'description' => $description,
					'fronting' => in_array($member['id'], $system['fronters']),
				];
			}
		}

		return $system;
	}

	private function constructPageEntry(): PageEntry {
		$system = $this->fetchSystem();
		$content = TwigContainer::get()->render('PluralKitSource.twig', [
			'system' => $system,
			'member_ids_fronting_first' => array_unique(array_merge(
				array_values($system['fronters']),
				array_keys($system['members']),
			)),
		]);

		return new PageEntry(
			$this,
			array_key_exists('slug', $this->config) ? $this->config['slug'] : '/system',
			"text/html",
			$content,
			[
				'title' => $system['name'],
			]
		);
	}

	public function hasEntry(string $slug): bool {
		$cfg_slug = array_key_exists('slug', $this->config) ? $this->config['slug'] : '/system';
		return $slug === $cfg_slug;
	}

	public function getEntry(string $slug): ?PageEntry {
		if ($this->hasEntry($slug)) {
			return $this->constructPageEntry();	
		}

		return null;
	}
}
