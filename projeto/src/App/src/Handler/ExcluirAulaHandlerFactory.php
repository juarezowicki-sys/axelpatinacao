<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Db\Adapter\AdapterInterface;
use Mezzio\Router\RouterInterface;
use Psr\Container\ContainerInterface;

class ExcluirAulaHandlerFactory
{
    public function __invoke(ContainerInterface $container) : ExcluirAulaHandler
    {
        return new ExcluirAulaHandler(
            $container->get(AdapterInterface::class),
            $container->get(RouterInterface::class)
        );
    }
}