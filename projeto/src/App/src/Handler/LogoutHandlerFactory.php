<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Container\ContainerInterface;

class LogoutHandlerFactory
{
    public function __invoke(ContainerInterface $container): LogoutHandler
    {
        // Pega as configurações globais do projeto
        $config = $container->get('config');
        
        // Passa apenas a parte do Google para o Handler
        $googleConfig = $config['google_auth'] ?? [];

        return new LogoutHandler($googleConfig);
    }
}