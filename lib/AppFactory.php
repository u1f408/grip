<?php declare(strict_types=1);

namespace Grip;

final class AppFactory {
	public static function create(): \Slim\App {
		$app = \Slim\Factory\AppFactory::create();
		$app->addErrorMiddleware(($_ENV['APP_ENV'] ?? 'production') !== 'production', false, false);
		$app->add(new \Middlewares\TrailingSlash(false));

		$app->get('/{slug:[^!]*}[!{opts:[a-z,]*}]', \Grip\PageController::class);

		return $app;
	}
}
