<?php

declare(strict_types=1);

namespace App\Handler;

use App\Authentication\MySqlUserRepository;
use Laminas\Db\Adapter\AdapterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Mezzio\Router\RouterInterface;

class ConfirmacaoHandlerFactory
{
    public function __invoke(ContainerInterface $container): ConfirmacaoHandler
    {
        return new ConfirmacaoHandler(
            $container->get(AdapterInterface::class),
            $container->get(TemplateRendererInterface::class),
            $container->get(MySqlUserRepository::class),
            $container->get(RouterInterface::class)
        );
    }
}