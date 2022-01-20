<?php declare(strict_types=1);

namespace Grip;
use Grip\ErrorRenderer;
use Grip\PageController;

final class AppFactory {
	public static function create(): \Slim\App {
		$app = \Slim\Factory\AppFactory::create();

		$errorMiddleware = $app->addErrorMiddleware(($_ENV['APP_ENV'] ?? 'production') !== 'production', false, false);
		$errorHandler = $errorMiddleware->getDefaultErrorHandler();
		$errorHandler->registerErrorRenderer('text/html', ErrorRenderer::class);

		$app->add(new \Middlewares\TrailingSlash(false));
		$app->get('/{slug:[^!]*}[!{opts:[a-z,]*}]', PageController::class);

		return $app;
	}
}
