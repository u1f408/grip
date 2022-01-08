<?php declare(strict_types=1);

namespace Grip\Renderer;

abstract class Renderer {
	abstract protected function render(string $content): string;
}
