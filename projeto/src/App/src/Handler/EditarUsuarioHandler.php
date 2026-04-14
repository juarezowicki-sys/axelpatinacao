<?php

declare(strict_types=1);

namespace App\Handler;

use App\Form\EditarUsuarioForm;
use Mezzio\Flash\FlashMessageMiddleware;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class EditarUsuarioHandler implements RequestHandlerInterface
{
    private $adapter;
    private $template;
    private $router;

    public function __construct(
        AdapterInterface $adapter,
        TemplateRendererInterface $template,
        RouterInterface $router
    ) {
        $this->adapter  = $adapter;
        $this->template = $template;
        $this->router   = $router;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $request->getAttribute('id');
        $message = '';
        $tableGateway = new TableGateway('usuarios', $this->adapter);
        $form = new EditarUsuarioForm();

        $session = $request->getAttribute(\Mezzio\Session\SessionMiddleware::SESSION_ATTRIBUTE);
        $userData = $session->get(\Mezzio\Authentication\UserInterface::class);
        // Extrai o nome do usuario (detalhes) e o role (primeira posição do array)
        $nomeLogado = $userData['details']['nome'] ?? null;
        $idLogado = $userData['details']['id'] ?? null;
        $roles = $userData['roles'] ?? [];
        $role = $roles[0] ?? 'guest';
        // 1. Obter o container de flash da requisição
        $flashMessages = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
                                
        // Busca o usuario no banco (retorna um ArrayObject ou false)
        /** @var \Laminas\Db\TableGateway $tableGateway */
        $usuario = $tableGateway->select(function ($select) use ($role, $id, $nomeLogado, $idLogado) {
            if ($role === 'admin' || $role === 'monitor') {
                //  Vê todos os usuarios
                $select->where(['id' => $id]);
            } elseif ($role === 'titular' || $role === 'usuario') {
                // Titular: Vê apenas usuarios onde a coluna 'nome' é o seu nome
                $select->where(['nome' => $nomeLogado, 'id' => $idLogado]);
            } else {
                // Guest ou sem role: Retorna lista vazia
                $select->where->equalTo(1, 0);
            }
        })->current();

        if (! $usuario) {
            return new RedirectResponse($this->router->generateUri('usuarios.listar'));
        }
        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $form->setData($data);
            if ($form->isValid()) {
                $validData = $form->getData();
                unset($validData['submit']); // Remove botão se existir no array
                try {
                    $tableGateway->update($validData, ['id' => $id]);

                    // 2. Criar uma flash message (chave, valor)
                    $flashMessages->flash('sucesso', 'O perfil do usuário foi editado com sucesso!', 1);

                    if ($role === 'admin') {
                        return new RedirectResponse($this->router->generateUri('usuarios.listar'));
                    } elseif ($role !== 'guest') {
                        return new RedirectResponse($this->router->generateUri('home.usuario'));
                    }
                } catch (\Laminas\Db\Adapter\Exception\InvalidQueryException $e) {
                    // Se der erro de "Duplicate entry" (Código 1062)
                    if (strpos($e->getMessage(), '1062') !== false) {
                        $message = "Ops! O telefone '{$data['telefone']}' já existe. Por favor, escolha outro.";
                    } else {
                        // Se for outro erro, exibe uma mensagem genérica
                        $message = "Erro ao salvar os dados. Tente novamente.";
                    }
                }
            }
        } else {
            // Se for GET, preenche o form com os dados vindos do banco e com as máscaras abaixo
            $usuario['telefone'] = strlen($usuario['telefone']) === 11
                ? vsprintf('(%s%s) %s%s%s%s%s-%s%s%s%s', str_split($usuario['telefone'])) // (00) 99999-9999
                : vsprintf('(%s%s) %s%s%s%s-%s%s%s%s', str_split($usuario['telefone'])); // (00) 9999-9999
            $usuario['documento'] = strlen($usuario['documento']) === 11
                ? vsprintf('%s%s%s.%s%s%s.%s%s%s-%s%s', str_split($usuario['documento']))
                : vsprintf("%s%s.%s%s%s.%s%s%s/%s%s%s%s-%s%s", str_split($usuario['documento']));

            if ($role !== 'admin') {
                $element01 = $form->get('nome');
                $element02 = $form->get('documento');
                $element04 = $form->get('role');
                $element01->setAttribute('readonly', true);
                $element02->setAttribute('readonly', true);
                $element04->setAttribute('class', 'hidden');
                $element04->setLabel(' ');
            }
            unset($usuario->password_reset_token);
            unset($usuario->password);
            unset($usuario->token_expiry);
            unset($usuario->data_cadastro);
            unset($usuario->status);
            unset($usuario->token_confirmacao);
            // Convertemos para array para garantir a compatibilidade com o formulário
            $form->setData((array) $usuario);
        }
        return new HtmlResponse($this->template->render('app::editar-usuario', [
            'form' => $form,
            'id'   => $id,
            'message' => $message
        ]));
    }
}
