<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Container\ContainerInterface;
use Mezzio\Template\TemplateRendererInterface;
use App\Middleware\TemplateDefaultsMiddleware;

class TemplateDefaultsMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): TemplateDefaultsMiddleware
    {
        // Pega o motor de templates (Plates/Twig) para injetar no Middleware
        $renderer = $container->get(TemplateRendererInterface::class);
        return new TemplateDefaultsMiddleware($renderer);
    }
}