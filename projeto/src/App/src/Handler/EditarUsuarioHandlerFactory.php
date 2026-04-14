<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Db\Adapter\AdapterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Mezzio\Router\RouterInterface;
use Psr\Container\ContainerInterface;

class EditarUsuarioHandlerFactory
{
    public function __invoke(ContainerInterface $container) : EditarUsuarioHandler
    {
        return new EditarUsuarioHandler(
            $container->get(AdapterInterface::class),
            $container->get(TemplateRendererInterface::class),
            $container->get(RouterInterface::class)
        );
    }
}