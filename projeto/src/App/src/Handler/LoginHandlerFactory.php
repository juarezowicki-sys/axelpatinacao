<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Container\ContainerInterface;
use Mezzio\Template\TemplateRendererInterface;
use Laminas\Db\Adapter\AdapterInterface;

class LoginHandlerFactory
{
    public function __invoke(ContainerInterface $container) : LoginHandler
    {
        // Esta linha busca a configuração que você acabou de me mostrar
        $adapter = $container->get(AdapterInterface::class);
        
        // Esta busca o renderer de templates
        $template = $container->get(TemplateRendererInterface::class);

        // INJETA os dois no Handler
        return new LoginHandler($adapter, $template);
    }
}
