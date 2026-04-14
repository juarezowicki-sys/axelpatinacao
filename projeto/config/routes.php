<?php

declare(strict_types=1);

use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Psr\Container\ContainerInterface;



return static function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {

    /*
    // 1. Exige APENAS que o usuário esteja logado (não checa RBAC)
    $soLogin = function (string $handlerClass) {
        return [
            Mezzio\Authentication\AuthenticationMiddleware::class,
            $handlerClass
        ];
    };

    // 2. Exige Login + Permissão específica no RBAC (O que você já usa)
    $restrito = function (string $handlerClass) {
        return [
            Mezzio\Authentication\AuthenticationMiddleware::class,
            Mezzio\Authorization\AuthorizationMiddleware::class,
            $handlerClass
        ];
    };
*/
    $app->get('/ping', App\Handler\PingHandler::class, 'api.ping');
    $app->get('/', App\Handler\HomePageHandler::class, 'home.page');
    $app->route('/login', App\Handler\LoginHandler::class, ['GET', 'POST'], 'login');
    $app->route('/novo_usuario', App\Handler\UsuarioHandler::class, ['GET', 'POST'], 'usuario');
    $app->get('/confirmar/{token}', App\Handler\ConfirmacaoHandler::class, 'usuario.confirmar');
    $app->route('/auth/google/callback', App\Handler\GoogleAuthHandler::class, ['GET', 'POST'], 'auth.google');

    $app->route('/minha_senha', App\Handler\ForgotPasswordHandler::class, ['GET', 'POST'], 'forgot.password');
    $app->route('/alterar_a_minha_senha', App\Handler\ResetPasswordHandler::class, ['GET', 'POST'], 'reset.password');

    //$app->get('/usuario', App\Handler\HomeUsuarioHandler::class, 'home.usuario');
    $app->route('/usuario[/{nome:.+}]', App\Handler\HomeUsuarioHandler::class, ['GET', 'POST'], 'home.usuario');

    $app->route('/editar_usuario/{id:\d+}', App\Handler\EditarUsuarioHandler::class, ['GET', 'POST'], 'usuario.editar');
    $app->route('/novo_atleta', App\Handler\AtletaHandler::class, ['GET', 'POST'], 'atleta');
    $app->route('/editar_atleta/{id:\d+}', App\Handler\EditarAtletaHandler::class, ['GET', 'POST'], 'atleta.editar');
    $app->get('/logout', App\Handler\LogoutHandler::class, 'logout');

    $app->route('/nova_turma', App\Handler\TurmaHandler::class, ['GET', 'POST'], 'turma');
    $app->get('/turmas', App\Handler\ListarTurmasHandler::class, 'turmas.listar');
    $app->route('/editar_turma/{id:\d+}', App\Handler\EditarTurmaHandler::class, ['GET', 'POST'], 'turma.editar');
    $app->get('/excluir_turma/{id:\d+}', App\Handler\ExcluirTurmaHandler::class, 'turma.excluir');

    $app->route('/chamada/{id:\d+}', App\Handler\ChamadaHandler::class, ['GET', 'POST'], 'turma.chamada');
    $app->route('/aulas[/{chama:presentes|ausentes}/{atleta:.+}]', App\Handler\ListarAulasHandler::class, ['GET', 'POST'], 'aulas.listar');

    $app->route('/editar_aula/{id:\d+}', App\Handler\EditarAulaHandler::class, ['GET', 'POST'], 'aula.editar');
    $app->get('/excluir_aula/{id:\d+}', App\Handler\ExcluirAulaHandler::class, 'aula.excluir');

    $app->get('/usuarios', App\Handler\ListarUsuariosHandler::class, 'usuarios.listar');
    $app->get('/excluir_um_usuario/{id:\d+}', App\Handler\ExcluirUsuarioHandler::class, 'usuario.excluir');

    $app->route('/atletas[/{titular:.+}]', App\Handler\ListarAtletasHandler::class, ['GET', 'POST'], 'atletas.listar');
    // $app->get('/atletas[/{nome:.+}]', App\Handler\ListarAtletasHandler::class, 'atletas.listar');
    $app->get('/excluir_um_atleta/{id:\d+}', App\Handler\ExcluirAtletaHandler::class, 'atleta.excluir');
};
