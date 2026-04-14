<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Container\ContainerInterface;
use Mezzio\Template\TemplateRendererInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Mezzio\Router\RouterInterface;

class ResetPasswordHandlerFactory
{
    public function __invoke(ContainerInterface $container): ResetPasswordHandler
    {
        // Pega o renderizador de templates (Twig, Plates, etc)
        $renderer = $container->get(TemplateRendererInterface::class);
        $adapter = $container->get(AdapterInterface::class);
        // Pega a conexão com o banco de dados (AdapterInterface)
        $db = $container->get(AdapterInterface::class);
          $router =   $container->get(RouterInterface::class);

        return new ResetPasswordHandler($renderer,  $adapter, $db, $router);
    }
}
