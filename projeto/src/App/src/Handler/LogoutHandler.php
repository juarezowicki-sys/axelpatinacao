<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Session\SessionMiddleware;
use Hybridauth\Hybridauth;

class LogoutHandler implements RequestHandlerInterface
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        // 2. Limpa o Mezzio Session
        if ($session) {
            // 2. Limpa os dados (Remove tudo do Mezzio e da global $_SESSION vinculada)
            $session->clear();

            // 3. Invalida a sessão atual (Isso gera um novo ID vazio com segurança)
            // No Mezzio, isso substitui o session_destroy() problemático
            if (method_exists($session, 'regenerate')) {
                $session->regenerate();
            }
        }
         $_SESSION = [];
        // 1. Desconecta o Hybridauth (Agora com $this->config)
        try {
            $hybridauth = new Hybridauth($this->config);
            $adapter = $hybridauth->getAdapter('Google');
            $adapter->disconnect();
        } catch (\Exception $e) {
            // Se falhar (ex: sessão já expirou), apenas ignora e segue o logout
        }
        // 3. Limpa a Global do PHP
       

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();     // Remove as variáveis
            session_destroy();   // Destrói o arquivo no XAMPP

            // FORÇA O PHP A ENCERRAR O ARQUIVO NO WINDOWS
            session_write_close();
        }

        // 5. Opcional: Se quiser forçar a expiração do cookie sem dar erro de ID
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        return new RedirectResponse('/');
    }
}
