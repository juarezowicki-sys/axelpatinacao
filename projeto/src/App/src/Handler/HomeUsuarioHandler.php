<?php

declare(strict_types=1);

namespace App\Handler;

use Mezzio\Flash\FlashMessageMiddleware;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HomeUsuarioHandler implements RequestHandlerInterface
{
    private $adapter;
    private $template;

    public function __construct(AdapterInterface $adapter, TemplateRendererInterface $template)
    {
        $this->adapter = $adapter;
        $this->template = $template;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute(\Mezzio\Session\SessionMiddleware::SESSION_ATTRIBUTE);
        $userData = $session->get(\Mezzio\Authentication\UserInterface::class);

        // No seu handler da página de destino ou diretamente na view
        $flashMessages = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
        $mensagensSucesso = $flashMessages->getFlash('sucesso');


        // Extrai o nome do titular (detalhes) e o role (primeira posição do array)
        $nomeLogado = $userData['details']['nome'] ?? null;
        $roles = $userData['roles'] ?? [];
        $role = $roles[0] ?? 'guest';
        // Pega o ID da URL

        $getNome = $request->getAttribute('nome');
        $getNome = isset($getNome) ? urldecode($getNome) : '';

        $tableGateway = new TableGateway('usuarios', $this->adapter);
        $usuarios = $tableGateway->select(function ($select) use ($getNome, $role, $nomeLogado) {
            if ($role === 'admin' && $getNome) {
                // Usuário vê apenas o usuario que o id veio por get
                $select->where(['nome' => $getNome]);
            } else if ($role !== 'guest' && $nomeLogado) {
                // Usuário vê apenas o usuario onde a coluna 'nome' é o seu próprio nome
                $select->where(['nome' => $nomeLogado]);
            } else {
                // Guest ou sem role: Retorna vazio
                $select->where->equalTo(1, 0);
            }
        });
         //var_dump($usuarios);exit;
        /** @var App\Handler $usuarios */
        return new HtmlResponse($this->template->render('app::home-usuario', [
            'usuarios' => $usuarios->toArray(), // Converte o ResultSet em Array
            'mensagensSucesso' => $mensagensSucesso
        ]));
    }
}
