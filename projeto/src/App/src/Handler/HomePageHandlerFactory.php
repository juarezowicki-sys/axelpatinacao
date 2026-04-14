<?php

declare(strict_types=1);

namespace App\Handler;

use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function assert;

final class HomePageHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        // 1. Coleta o Router
        $router = $container->get(RouterInterface::class);

        // 2. Coleta o Template (com verificação)
        $template = $container->has(TemplateRendererInterface::class)
            ? $container->get(TemplateRendererInterface::class)
            : null;

        // 3. Coleta o Adaptador do Banco de Dados
        $adapter = $container->get(\Laminas\Db\Adapter\AdapterInterface::class);

        // 4. RETORNA UMA VEZ SÓ, passando tudo para o Handler
        // Verifique se a ordem dos argumentos aqui bate com a ordem no __construct do HomePageHandler
        return new HomePageHandler($template);
    }
}
