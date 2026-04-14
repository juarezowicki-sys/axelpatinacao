<?php

declare(strict_types=1);

namespace App\Handler;

use Exception;
use Hybridauth\Hybridauth;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGateway;
use Mezzio\Authentication\DefaultUser;
use Mezzio\Authentication\UserInterface;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\Response\HtmlResponse;

class GoogleAuthHandler implements RequestHandlerInterface
{
    public function __construct(
        private AdapterInterface $adapter,
        private array $config
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // 1f Recupera a sessão do Mezzio
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        if (!$session instanceof SessionInterface) {
            return new HtmlResponse("Erro: Sessão não inicializada.", 500);
        }

        // 2. Sincroniza Mezzio -> Global $_SESSION (Necessário para Hybridauth)
        foreach ($session->toArray() as $key => $value) {
            $_SESSION[$key] = $value;
        }

        // 3. Configuração do Provider
        $config = [
            'callback' => $this->config['callback'],
            'providers' => [
                'Google' => [
                    'enabled' => true,
                    'keys' => [
                        'id'     => $this->config['keys']['id'],
                        'secret' => $this->config['keys']['secret'],
                    ],
                    'authorize_url_parameters' => [
                        'prompt' => 'select_account',
                    ],
                ],
            ],
        ];

        try {
            $hybridauth = new Hybridauth($config);
            $adapter = $hybridauth->authenticate('Google');

            // 4. Sincroniza Global $_SESSION -> Mezzio (Grava o que o Google devolveu)
            if (isset($_SESSION) && is_array($_SESSION)) {
                foreach ($_SESSION as $key => $value) {
                    $session->set($key, $value);
                }
            }

            $userProfile = $adapter->getUserProfile();

            // 5. Busca no banco de dados
            $tableGateway = new TableGateway('usuarios', $this->adapter);
            $resultSet = $tableGateway->select(['email' => $userProfile->email]);
            $user = $resultSet->current();

            $uri = $request->getUri();

            if (!$user) {
                $session->unset('HA::STORE');
                $urlDestino = sprintf('%s://%s/login?msg=nao_consta', $uri->getScheme(), $uri->getHost());
                return new RedirectResponse($urlDestino);
            }

            // 6. Define a Identidade no Mezzio
            $identity = new DefaultUser(
                $user['email'],
                [(string)$user['role']],
                [
                    'id'   => $user['id'],
                    'nome' => $user['nome'],
                    'role' => $user['role']
                ]
            );

            $session->set(UserInterface::class, [
                'username' => $identity->getIdentity(),
                'roles'    => (array) $identity->getRoles(),
                'details'  => $identity->getDetails(),
            ]);

            $adapter->disconnect();

            $urlDestino = sprintf('%s://%s/usuario', $uri->getScheme(), $uri->getHost());
            return new RedirectResponse($urlDestino);

        } catch (Exception $e) {
            // --- Lógica de Auto-Reset para erro de State/Cookie ---
            $session->clear();
            $_SESSION = [];

            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, 
                    $params["path"], $params["domain"], 
                    $params["secure"], $params["httponly"]
                );
            }

            $uri = $request->getUri();
            $urlRetry = sprintf('%s://%s/login?retry=1', $uri->getScheme(), $uri->getHost());
            return new RedirectResponse($urlRetry);
        }
    }
}