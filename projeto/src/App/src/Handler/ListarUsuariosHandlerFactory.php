<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Db\Adapter\AdapterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class ListarUsuariosHandlerFactory
{
    public function __invoke(ContainerInterface $container) : ListarUsuariosHandler
    {
        $adapter  = $container->get(AdapterInterface::class);
        $template = $container->get(TemplateRendererInterface::class);

        return new ListarUsuariosHandler($adapter, $template);
    }
}