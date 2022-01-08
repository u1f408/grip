<?php declare(strict_types=1);

namespace Grip\Renderer;
use Grip\Renderer\Renderer;
use Michelf\MarkdownExtra;

class MarkdownRenderer extends Renderer {
	public function render(string $content): string {
		return MarkdownExtra::defaultTransform($content);
	}
}
