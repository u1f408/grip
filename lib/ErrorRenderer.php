<?php declare(strict_types=1);

namespace Grip;
use Grip\TwigContainer;
use Grip\Configuration;
use Slim\Interfaces\ErrorRendererInterface;

class ErrorRenderer implements ErrorRendererInterface {
	public function __invoke(\Throwable $exception, bool $displayErrorDetails): string {
		return TwigContainer::get()->render("ErrorRenderer.twig", [
			'exception' => $exception,
			'displayErrorDetails' => $displayErrorDetails,
			'site' => [
				'var' => Configuration::$site,
				'raw_config' => Configuration::$raw_config,
				'sources' => Configuration::$sources,
			],
		]);
	}
}
