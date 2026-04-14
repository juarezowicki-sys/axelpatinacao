<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Container\ContainerInterface;
use Mezzio\Helper\UrlHelper;

class AuthorizationErrorMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): AuthorizationErrorMiddleware
    {
        $urlHelper = $container->get(UrlHelper::class);
        return new AuthorizationErrorMiddleware($urlHelper);
    }
}