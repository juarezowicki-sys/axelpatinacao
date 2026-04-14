<?php

declare(strict_types=1);

namespace App\Handler;

use App\Authentication\MySqlUserRepository;
use Mezzio\Flash\FlashMessageMiddleware;
use Laminas\Db\Sql\Sql;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Mezzio\Session\SessionInterface;
use Mezzio\Authentication\UserInterface;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Db\Adapter\AdapterInterface;
use Mezzio\Router\RouterInterface;
use phpDocumentor\Reflection\Types\This;

class ConfirmacaoHandler implements RequestHandlerInterface
{
    public function __construct(
        private AdapterInterface $adapter,
        private TemplateRendererInterface $template,
        private MySqlUserRepository $userRepository,
        private RouterInterface $router
    ) {
        $this->router   = $router;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Pega o token que vem na URL (/confirmar/TOKEN_AQUI)
        $token = $request->getAttribute('token', '');

        if (empty($token)) {
            return new HtmlResponse("Link inválido.");
        }

        // Busca o usuário no banco pelo token
        $user = $this->userRepository->findByToken($token);

        if (! $user) {
            return new HtmlResponse("Este link de confirmação já expirou ou é inválido.");
        }

        // Ativa o usuário (status = 1) e remove o token para não ser usado de novo
        $sql = new Sql($this->adapter);
        $update = $sql->update('usuarios');
        $update->set([
            'status' => 1,
            'token_confirmacao' => null
        ]);
        $update->where(['id' => $user['id']]);

        $sql->prepareStatementForSqlObject($update)->execute();

        // 1. Pega a sessão do request
        $session = $request->getAttribute(SessionInterface::class);

        // 2. Prepara os dados exatamente como o Mezzio Authentication espera
        $session->set(UserInterface::class, [
            'username' => $user['email'], // ou o campo que você usa como login
            'roles'    => ['titular'],      // ou a role padrão do usuário
            'details'  => [
                'id'   => (int) $user['id'],
                'nome' => $user['nome']
            ]
        ]);
        // 1. Obter o contêiner de flash da requisição
        $flashMessages = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
        // 2. Criar uma flash message (chave, valor)
       $username = $user['nome'];
        $flashMessages->flash(
            'sucesso',
            'Conta Ativada! Olá, ' . $username . ' ! Sua conta foi confirmada com sucesso. Você é muito bem-vindo à Axel patinaçao! Utilize o link ao lado ou acima para registrar seu atleta ou acompanhar seu histórico nas nossas aulas.',
            1);
        return new RedirectResponse($this->router->generateUri('home.usuario'));
    }
}