<?php declare(strict_types=1);

namespace Grip;
use Grip\Configuration;
use Grip\Source\Source;
use Grip\Renderer\RendererFactory;

class PageEntry {
	/** @var []string PASSTHROUGH_MIME_TYPES */
	const PASSTHROUGH_MIME_TYPES = [
		"text/plain",
		"text/html",
		"text/css",
	];

	/** @var Source $source */
	private $source;

	/** @var string $slug */
	public $slug;

	/** @var string $mime_type */
	public $mime_type;

	/** @var ?string $content */
	public $content;

	/** @var array<string, mixed> $metadata */
	public $metadata;

	public function __construct(Source $source, string $slug, string $mime_type, string $content, ?array $metadata = null) {
		$this->source = $source;
		$this->slug = $slug;
		$this->mime_type = $mime_type;
		$this->content = $content;
		$this->metadata = $metadata ?? [];
	}

	public function renderContent(): array {
		if (in_array($this->mime_type, self::PASSTHROUGH_MIME_TYPES)) {
			return [
				'content' => $this->content,
				'mime_type' => $this->mime_type,
			];
		}

		$renderer = RendererFactory::create($this->mime_type);
		$content = $renderer->render($this->content);

		return [
			'content' => $content,
			'mime_type' => 'text/html',
		];
	}

	public function render(): array {
		$content = $this->renderContent();
		$template = "layout.twig";
		if (array_key_exists("template", $this->metadata)) {
			$template = $this->metadata["template"];
		}

		// Allow no-template pages
		if ($template === false || $content['mime_type'] !== 'text/html') {
			return $content;
		}

		// Render the containing template
		$rendered = TwigContainer::get()->render($template, [
			'content' => $content['content'],
			'page' => $this->metadata,
			'source' => $this->source,
			'slug' => $this->slug,
			'site' => [
				'var' => Configuration::$site,
				'raw_config' => Configuration::$raw_config,
				'sources' => Configuration::$sources,
			],
		]);

		return [
			'content' => $rendered,
			'mime_type' => 'text/html',
		];
	}
}
