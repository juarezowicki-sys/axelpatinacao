<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Container\ContainerInterface;
use Laminas\Db\Adapter\AdapterInterface;

class GoogleAuthHandlerFactory
{
    public function __invoke(ContainerInterface $container): GoogleAuthHandler
    {
        $config = $container->get('config');
        return new GoogleAuthHandler(
            $container->get(AdapterInterface::class),
            $config['google_auth'] // Pega o bloco que você salvou no local.php
        );
    }
}