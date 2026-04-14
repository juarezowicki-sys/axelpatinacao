<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Container\ContainerInterface;

class MailServiceFactory
{
    public function __invoke(ContainerInterface $container): MailService
    {
        // Aqui o Mezzio pega todo o array de configuração do sistema
        $config = $container->get('config');
        
        // E aqui pegamos apenas a parte 'mail_config' (do seu mail.local.php) 
        // para entregar ao MailService
        return new MailService($config['mail_config']);
    }
}