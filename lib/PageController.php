<?php declare(strict_types=1);

namespace Grip;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Grip\Source\SourceSearcher;
use Grip\PageEntry;

class PageController {
	private $container;

	public function __construct(?ContainerInterface $container) {
		$this->container = $container;
	}

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
		$opts = explode(',', $args['opts'] ?? '');

		// Try to find a source with the requested slug
		$slug = '/' . $args['slug'];
		if (($entry = SourceSearcher::getEntry($slug)) === null) {
			throw new HttpNotFoundException($request);
		}

		// Allow getting partial content
		if (in_array('partial', $opts)) {
			$content = $entry->renderContent();
		} else {
			$content = $entry->render();
		}

		$response = $response->withHeader('Content-Type', $content['mime_type']);
		$response->getBody()->write($content['content']);
		return $response;
	}
}
