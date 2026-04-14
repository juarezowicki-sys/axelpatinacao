<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Db\Adapter\AdapterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class HomeUsuarioHandlerFactory
{
   public function __invoke(ContainerInterface $container) : HomeUsuarioHandler
    {
        $adapter  = $container->get(AdapterInterface::class);
        $template = $container->get(TemplateRendererInterface::class);

        return new HomeUsuarioHandler($adapter, $template);
    }
}