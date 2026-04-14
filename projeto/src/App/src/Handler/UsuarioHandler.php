<?php

declare(strict_types=1);

namespace App\Handler;

use Mezzio\Router\RouterInterface;
use App\Authentication\MySqlUserRepository;
use App\Form\UsuarioForm;
use Laminas\Db\Adapter\AdapterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;

class UsuarioHandler implements RequestHandlerInterface
{
    private $adapter;
    private $template;
    private $userRepository;
    private $router;
    private $mailService;

    public function __construct(
        AdapterInterface $adapter,
        TemplateRendererInterface $template,
        MySqlUserRepository $userRepository,
        RouterInterface $router,
        \App\Service\MailService $mailService
    ) {
        $this->adapter = $adapter;
        $this->template = $template;
        $this->userRepository = $userRepository;
        $this->router = $router;
        $this->mailService = $mailService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {

        $session = $request->getAttribute(\Mezzio\Session\SessionMiddleware::SESSION_ATTRIBUTE);
        $userData = $session->get(\Mezzio\Authentication\UserInterface::class);
        // Extrai  o role (primeira posição do array)
        $roles = $userData['roles'] ?? [];
        $role = $roles[0] ?? 'guest';

        $form = new UsuarioForm('usuario', ['db_adapter' => $this->adapter]);
        $message = '';

        if ($request->getMethod() === 'POST') {

            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                $data = $form->getData();

                // 1. Verifica E-mail
                $emailExists = $this->userRepository->findByEmail($data['email']);

                // 2. Verifica CPF/CNPJ  no seu Repository 
                $cpfExists = method_exists($this->userRepository, 'findByCpf')
                    ? $this->userRepository->findByCpf($data['documento'])
                    : false;

                if ($emailExists) {
                    $form->get('email')->setMessages(['Este e-mail já está cadastrado.']);
                    $message = 'Erro: Usuário já existe.';
                } elseif ($cpfExists) {
                    $form->get('documento')->setMessages(['Este CPF/CNPJ já está cadastrado.']);
                    $message = 'Erro: Documento duplicado.';
                } else {
                    $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

                    if (!isset($role) || $role !== 'admin') {
                        // 1. Gera Token
                        $token = bin2hex(random_bytes(32));
                        $data['token_confirmacao'] = $token;
                        $data['status'] = 0;
                    } else {
                        $data['status'] = 1;
                    }

                    $data['role'] = 'titular';
                    unset($data['password_confirm']);
                    unset($data['submit']);

                    $tableGateway = new \Laminas\Db\TableGateway\TableGateway('usuarios', $this->adapter);

                    try {
                        foreach ($data as $key => $value) {
                            if ($value === '') $data[$key] = null;
                        }

                        // 2. Salva no Banco de Dados
                        $tableGateway->insert($data);

                         if (!isset($role) || $role !== 'admin') {
                            $uri = $request->getUri();
                            $baseUrl = $uri->getScheme() . '://' . $uri->getHost();

                            // Adiciona a porta APENAS se não for a padrão (80 ou 443)
                            if ($uri->getPort()) {
                                $baseUrl .= ':' . $uri->getPort();
                            }

                            $path = $this->router->generateUri('usuario.confirmar', ['token' => $token]);
                            $linkCompleto = $baseUrl . $path;

                            // 4. ENVIO REAL: Dispara o e-mail usando o MailService injetado
                            $this->mailService->sendConfirmation($data['email'], $linkCompleto);

                            $message = "Cadastro realizado! Enviamos um link de ativação para " . $data['email'] . " - não deixe de conferir também na sua caixa de spam";
                        } else {
                            // 1. Obter o contêiner de flash da requisição
                            $flashMessages = $request->getAttribute(\Mezzio\Flash\FlashMessageMiddleware::FLASH_ATTRIBUTE);
                            // 2. Criar uma flash message (chave, valor)
                            $flashMessages->flash('sucesso', 'Cadastro realizado com sucesso!');
                            return new \Laminas\Diactoros\Response\RedirectResponse($this->router->generateUri('usuarios.listar'));
                        }

                        // Limpa o form para o sucesso
                        $form = new \App\Form\UsuarioForm('usuario', ['db_adapter' => $this->adapter]);
                    } catch (\Exception $e) {
                        return new \Laminas\Diactoros\Response\HtmlResponse("<h1>Erro no cadastro: " . $e->getMessage() . "</h1>");
                    }
                }
            } else {
                $message = 'Corrija os erros em vermelho no formulário.';
            }
        }

        return new HtmlResponse($this->template->render('app::usuario', [
            'form'    => $form,
            'request' => $request,
            'message' => $message,
        ]));
    }
}
