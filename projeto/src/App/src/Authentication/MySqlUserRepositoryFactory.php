<?php

declare(strict_types=1);

namespace App\Authentication;

use Psr\Container\ContainerInterface;
use Laminas\Db\Adapter\AdapterInterface;

class MySqlUserRepositoryFactory
{
    public function __invoke(ContainerInterface $container): MySqlUserRepository
    {
        // Aqui o container busca o adaptador de banco que você já usa nos CRUDs
        $db = $container->get(AdapterInterface::class);
        
        return new MySqlUserRepository($db);
    }
}