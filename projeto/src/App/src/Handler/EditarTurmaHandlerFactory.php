<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Container\ContainerInterface;
use function assert;

final class EditarTurmaHandlerFactory
{
    public function __invoke(ContainerInterface $container): EditarTurmaHandler
    {
        // Busca o Adapter do banco de dados no Container
        $adapter = $container->get(\Laminas\Db\Adapter\AdapterInterface::class);

        // Busca o Router e o Template
        $router  = $container->get(\Mezzio\Router\RouterInterface::class);
        $template = $container->get(\Mezzio\Template\TemplateRendererInterface::class);

        // Passa tudo para o construtor do Handler
        return new EditarTurmaHandler($adapter, $router, $template);
    }
}
