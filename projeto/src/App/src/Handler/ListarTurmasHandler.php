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
use Mezzio\Session\SessionInterface;

class ListarTurmasHandler implements RequestHandlerInterface
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

        $flashMessages = $request->getAttribute(\Mezzio\Flash\FlashMessageMiddleware::FLASH_ATTRIBUTE);

        if (isset($role) && ($role === 'admin' || $role === 'monitor')) {

            $tableGateway = new TableGateway('turmas', $this->adapter);
            $turmas = $tableGateway->select(function ($select) use ($role, $nomeLogado) {
                if ($role === 'monitor' && $nomeLogado) {
                    // Usuário Vê apenas turmas onde a coluna 'monitor' é o seu nome
                    $select->where(['monitor' => $nomeLogado]);
                    $select->order('nome ASC');
                } elseif ($role === 'admin') {
                    $select->order('nome ASC');
                } else {
                    // Guest ou sem role: Retorna lista vazia
                    $select->where->equalTo('nome', '0');
                }
            });

            // No seu handler da página de destino ou diretamente na view       
            $mensagensSucesso = $flashMessages->getFlash('sucesso');
            $mensagensErro = $flashMessages->getFlash('erro');
            /** @var App\Handler $turmas */
            return new HtmlResponse($this->template->render('app::listar-turmas', [
                'role' => $role,
                'turmas' =>  $turmas->toArray(),
                'mensagensSucesso' => $mensagensSucesso,
                'mensagensErro' => $mensagensErro
            ]));
        }

        $mensagensErro = $flashMessages->flashNow('erro', 'Somente o administrador ou um monitor podem utilizar esta página', 1);
        $mensagensErro = $flashMessages->getFlash('erro');

        /** @var App\Handler $turmas */
        return new HtmlResponse($this->template->render('app::listar-turmas', [
            'role' => $role,
            'mensagensErro' => $mensagensErro
        ]));
    }
}
