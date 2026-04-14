<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ListarUsuariosHandler implements RequestHandlerInterface
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

        // Extrai o nome do titular (detalhes) e o role (primeira posição do array)
        $nomeLogado = $userData['details']['nome'] ?? null;
        $roles = $userData['roles'] ?? [];
        $role = $roles[0] ?? 'guest';

        $tableGateway = new TableGateway('usuarios', $this->adapter);
        $usuarios = $tableGateway->select(function ($select) use ($role, $nomeLogado) {
            if ($role === 'admin' || $role === 'monitor') {
                //  Vê todos os usuarios
                $select->order('nome ASC');
            } elseif ($role === 'titular' && $nomeLogado) {
                // Titular: Vê apenas usuarios onde a coluna 'nome' é o seu nome
                $select->where(['nome' => $nomeLogado]);
            } else {
                // Guest ou sem role: Retorna lista vazia
                $select->where->equalTo('nome', '0');
            }
        });
        
        $flashMessages = $request->getAttribute(\Mezzio\Flash\FlashMessageMiddleware::FLASH_ATTRIBUTE);
        // No seu handler da página de destino ou diretamente na view       
        $mensagensSucesso = $flashMessages->getFlash('sucesso');
        //var_dump($mensagensSucesso);exit;
        /** @var App\Handler $usuarios */
        return new HtmlResponse($this->template->render('app::listar-usuarios', [
            'usuarios' => $usuarios->toArray(), // Converte o ResultSet em Array
            'mensagensSucesso' => $mensagensSucesso
        ]));
    }
}
