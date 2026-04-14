<?php
declare(strict_types=1);

namespace App\Handler;

use Laminas\Db\Adapter\AdapterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class ListarAulasHandlerFactory
{
    public function __invoke(ContainerInterface $container) : ListarAulasHandler
    {
        $adapter  = $container->get(AdapterInterface::class);
        $template = $container->get(TemplateRendererInterface::class);

        return new ListarAulasHandler($adapter, $template);
    }
}