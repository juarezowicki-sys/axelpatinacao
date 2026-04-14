<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Db\Adapter\AdapterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Mezzio\Router\RouterInterface;

class ChamadaHandlerFactory
{
    public function __invoke(ContainerInterface $container): ChamadaHandler
    {
        // Puxa o adaptador do banco de dados configurado no Mezzio
        $adapter = $container->get(AdapterInterface::class);

        // Puxa o renderizador de templates (Twig, Plates ou LaminasView)
        $template = $container->get(TemplateRendererInterface::class);

        $router = $container->get(RouterInterface::class);

        return new ChamadaHandler($adapter, $template, $router);
    }
}
