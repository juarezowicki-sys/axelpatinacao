<?php

declare(strict_types=1);

namespace App\Handler;

use App\Authentication\MySqlUserRepository;
use Laminas\Db\Adapter\AdapterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class UsuarioHandlerFactory
{
    public function __invoke(ContainerInterface $container): UsuarioHandler
    {     // die("A Factory do UsuarioHandler foi chamada!"); 
        return new UsuarioHandler(
            $container->get(AdapterInterface::class),
            $container->get(TemplateRendererInterface::class),
            $container->get(MySqlUserRepository::class),
            $container->get(\Mezzio\Router\RouterInterface::class),
            $container->get(\App\Service\MailService::class)
        );
    }
}
