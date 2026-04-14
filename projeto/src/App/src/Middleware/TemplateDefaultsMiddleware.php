<?php

declare(strict_types=1);

namespace App\Middleware;

use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Authentication\User;

class TemplateDefaultsMiddleware implements MiddlewareInterface
{
    public function __construct(private TemplateRendererInterface $renderer) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var \Mezzio\Session\SessionInterface $session */
        $session = $request->getAttribute(\Mezzio\Session\SessionMiddleware::SESSION_ATTRIBUTE);

        // Tenta pegar os dados que você gravou manualmente no LoginHandler
        $userData = $session ? $session->get(\Mezzio\Authentication\UserInterface::class) : null;

        $name = 'Visitante';
        $role = 'guest';

        if ($userData && isset($userData['details']['nome'])) {
            $name = $userData['details']['nome'];
            $role = is_array($userData['roles']) ? current($userData['roles']) : $userData['roles'];
        }

        $this->renderer->addDefaultParam(\Mezzio\Template\TemplateRendererInterface::TEMPLATE_ALL, 'user_name', $name);
        $this->renderer->addDefaultParam(\Mezzio\Template\TemplateRendererInterface::TEMPLATE_ALL, 'user_role', $role);

        return $handler->handle($request);
    }
}
