<?php

declare(strict_types=1);

namespace App\Middleware;

use Mezzio\Helper\UrlHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Router\RouteResult;

class AuthorizationErrorMiddleware implements MiddlewareInterface
{
    private UrlHelper $urlHelper;

    // O construtor recebe a dependência
    public function __construct(UrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $routeResult = $request->getAttribute(RouteResult::class);
        $routeName   = $routeResult ? $routeResult->getMatchedRouteName() : null;

        if (in_array($response->getStatusCode(), [401, 403, 404]) && $routeName !== 'login') {
            // Gera a URL dinamicamente pelo nome da rota
            return new RedirectResponse($this->urlHelper->generate('login'));
        }

        return $response;
    }
}