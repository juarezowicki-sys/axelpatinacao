<?php

declare(strict_types=1);

namespace App\Handler;

use Mezzio\Flash\FlashMessageMiddleware;
use Laminas\Db\TableGateway\TableGateway;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Template\TemplateRendererInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Mezzio\Authentication\DefaultUser;
use Mezzio\Authentication\UserInterface;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Router\RouterInterface;
use Laminas\Db\Sql\Sql;

class ResetPasswordHandler implements RequestHandlerInterface
{
    public function __construct(
        private TemplateRendererInterface $renderer,
        private AdapterInterface $db,
        private AdapterInterface $adapter,
        private RouterInterface $router
    )  {
        $this->router   = $router;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $postParams = $request->getParsedBody();
        $token = $postParams['token'] ?? $queryParams['token'] ?? '';



        $sql = new Sql($this->db);
        $select = $sql->select('usuarios')->where(['password_reset_token' => $token]);
        $user = $sql->prepareStatementForSqlObject($select)->execute()->current();

        // Se token não existe ou expirou, manda pro login com erro amigável
        if (! $user || strtotime($user['token_expiry']) < time()) {
            return new RedirectResponse('/login?error=link-expirado');
        }

        if ($request->getMethod() === 'POST') {

            $newPassword = $postParams['new_password'] ?? '';
            $confirmPassword = $postParams['confirm_password'] ?? '';

            // TRAVA DE SEGURANÇA: Mínimo de 6 caracteres e confirmação idêntica
            if (strlen($newPassword) < 6) {
                return new HtmlResponse($this->renderer->render('app::reset-password', [
                    'token' => $token,
                    'error' => 'A senha deve ter pelo menos 6 caracteres.'
                ]));
            }

            if ($newPassword !== $confirmPassword) {
                return new HtmlResponse($this->renderer->render('app::reset-password', [
                    'token' => $token,
                    'error' => 'As senhas não coincidem.'
                ]));
            }

            $hash = password_hash($newPassword, PASSWORD_BCRYPT);

            $update = $sql->update('usuarios')
                ->set([
                    'password' => $hash,
                    'password_reset_token' => null,
                    'token_expiry' => null
                ])
                ->where(['password_reset_token' => $token]);
            $sql->prepareStatementForSqlObject($update)->execute();

            $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

            $tableGateway = new TableGateway('usuarios', $this->adapter);
            /** @var \Laminas\Db\ResultSet\ResultSet $resultSet */
            $resultSet = $tableGateway->select(['password' => $hash]);
            $userData = $resultSet->current();

            $identity = new DefaultUser(
                $userData['email'],
                [(string)$userData['role']],
                ['id' => $userData['id'], 'nome' => $userData['nome']]
            );

            $session->set(UserInterface::class, [
                'username' => $identity->getIdentity(),
                'roles'    => (array) $identity->getRoles(),
                'details'  => $identity->getDetails(),
            ]);

            // 1. Obter o contêiner de flash da requisição
            $flashMessages = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
            // 2. Criar uma flash message (chave, valor)
            $username = $userData['nome'];
            $flashMessages->flash(
                'sucesso',
                'Senha alterada! Olá, ' . $username . ' ! Sua senha foi alterada conforme solicitado, e ela já está valendo. Você está logado no sistema. Nas próximas visitas ao nosso site, utilize a nova senha.',
                1);
            return new RedirectResponse($this->router->generateUri('home.usuario'));
        }

        return new HtmlResponse($this->renderer->render('app::reset-password', ['token' => $token]));
    }
}
