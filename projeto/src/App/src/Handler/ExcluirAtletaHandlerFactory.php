<?php
declare(strict_types=1);

namespace App\Handler;

use Laminas\Db\Adapter\AdapterInterface;
use Mezzio\Router\RouterInterface;
use Psr\Container\ContainerInterface;

class ExcluirAtletaHandlerFactory
{
    public function __invoke(ContainerInterface $container) : ExcluirAtletaHandler
    {
        return new ExcluirAtletaHandler(
            $container->get(AdapterInterface::class),
            $container->get(RouterInterface::class)
        );
    }
}