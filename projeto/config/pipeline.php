<?php

declare(strict_types=1);

use Laminas\Stratigility\Middleware\ErrorHandler;
use Mezzio\Application;
use Mezzio\Handler\NotFoundHandler;
use Mezzio\Helper\ServerUrlMiddleware;
use Mezzio\Helper\UrlHelperMiddleware;
use Mezzio\MiddlewareFactory;
use Mezzio\Router\Middleware\DispatchMiddleware;
use Mezzio\Router\Middleware\ImplicitHeadMiddleware;
use Mezzio\Router\Middleware\ImplicitOptionsMiddleware;
use Mezzio\Router\Middleware\MethodNotAllowedMiddleware;
use Psr\Container\ContainerInterface;

return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {

    /*
    $app->pipe(function ($request, $handler) use ($container) {
        $path = $request->getUri()->getPath();

        // Se o caminho for exatamente '/login', deixe passar sem autenticar
        if ($path === '/login') {
            return $handler->handle($request);
        }

        // Para todas as outras rotas, execute o middleware de autenticação
        $auth = $container->get(Mezzio\Authentication\AuthenticationMiddleware::class);
        return $auth->process($request, $handler);
    });
*/

    // 1. Tratamento de Erros (deve ser o primeiro)
    $app->pipe(ErrorHandler::class);

    // 2. Servir arquivos estáticos (se necessário)
    // $app->pipe(ServeStaticMiddleware::class);

    // 3. Helper de URL
    $app->pipe(ServerUrlMiddleware::class);

    // 3. O de Rotas deve vir antes de todos  para que o $routeName não seja nulo
    $app->pipe(\Mezzio\Router\Middleware\RouteMiddleware::class);

    $app->pipe(\Mezzio\Session\SessionMiddleware::class);
    $app->pipe(\Mezzio\Flash\FlashMessageMiddleware::class);

    // 5. Autenticação/Autorização (após rotas)
    // $app->pipe(\Mezzio\Authentication\AuthenticationMiddleware::class);
    $app->pipe(function ($request, $handler) use ($container) {
        $auth = $container->get(\Mezzio\Authentication\AuthenticationInterface::class);
        $user = $auth->authenticate($request);

        if (null !== $user) {
            // Se autenticou, anexa o usuário real
            return $handler->handle($request->withAttribute(\Mezzio\Authentication\UserInterface::class, $user));
        }

        // Se falhou (guest), cria um objeto de usuário anônimo
        $guestUser = new \Mezzio\Authentication\DefaultUser('guest', ['guest']);
        return $handler->handle($request->withAttribute(\Mezzio\Authentication\UserInterface::class, $guestUser));
    });
    $app->pipe(\App\Middleware\AuthorizationErrorMiddleware::class);
    $app->pipe(\Mezzio\Authorization\AuthorizationMiddleware::class);

    // 2º OBRIGATÓRIO: Só agora ele pode pegar o nome e jogar no template
    $app->pipe(\App\Middleware\TemplateDefaultsMiddleware::class);
    $app->pipe(ImplicitHeadMiddleware::class);
    $app->pipe(ImplicitOptionsMiddleware::class);
    $app->pipe(MethodNotAllowedMiddleware::class);
    $app->pipe(UrlHelperMiddleware::class);
    $app->pipe(DispatchMiddleware::class);

    $app->pipe(NotFoundHandler::class);
};