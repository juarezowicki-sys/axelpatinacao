<?php

declare(strict_types=1);

namespace App\Handler;


use Laminas\Db\TableGateway\TableGateway;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Authentication\DefaultUser;
use Mezzio\Authentication\UserInterface;
use Mezzio\Session\SessionMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LoginHandler implements RequestHandlerInterface
{
    private $adapter;
    private $template;

    public function __construct($adapter, $template)
    {
        $this->adapter  = $adapter;
        $this->template = $template;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        $error = null;

        // Se já houver alguém logado,  limpa a $session 
        //  if ($session->has(UserInterface::class)) {

        //   $session->clear();
        //  $error = "Neste dispositivo, já existe um usuário logado no nosso site, você pode clicar no link 'Logout' no menu,  e após isso executar seu login, se precisar.";
        // 4. ENVIA O ERRO PARA A pag do usuario
        //return new HtmlResponse($this->template->render('app::home-usuario', [
        //'message' => $error
        //]));
        //  }  
if ($request->getMethod() === 'POST') {
    $params = $request->getParsedBody();
    $password = trim($params['password'] ?? '');
    $username = trim($params['username'] ?? '');

    // Limpa a string para buscar apenas os números no campo de telefone
    $telefoneLimpo = preg_replace('/\D/', '', $username);

    $tableGateway = new TableGateway('usuarios', $this->adapter);
/** @var \Laminas\Db\ResultSet\ResultSet $resultSet */
    $resultSet = $tableGateway->select(function ($select) use ($username, $telefoneLimpo) {
        $select->where->nest()
            ->equalTo('email', $username) // Busca exata do e-mail
            ->or
            ->equalTo('telefone', $telefoneLimpo) // Busca exata do telefone (só números)
            ->unnest();
    });

    $userData = $resultSet->current();
    /*    if ($request->getMethod() === 'POST') {
            $params = $request->getParsedBody();
            $password = trim($params['password'] ?? '');
            $username = trim($params['username'] ?? '');
            $foneDocum = preg_replace('/\D/', '', $username ?? '');

            $tableGateway = new TableGateway('usuarios', $this->adapter);
            /** @var \Laminas\Db\ResultSet\ResultSet $resultSet */
            // $resultSet = $tableGateway->select(['email' => $email]);
   /*   $resultSet = $tableGateway->select(function ($select) use ($username, $foneDocum) {
                $select->where->nest()
                    ->equalTo('email', $username)
                    ->or
                    ->equalTo('telefone', $foneDocum)
                    ->or
                    ->equalTo('documento', $username)
                    ->unnest();
            });
            $userData = $resultSet->current();
*/
            if (! $userData) {
                $error = 'Senha ou login incorretos';
            } else {
                // 2. VERIFICAÇÃO DE STATUS (Trava de segurança)
                if ((int)$userData['status'] !== 1) {
                    $error = 'Sua conta ainda não foi ativada. Verifique seu e-mail.';
                }
                // 3. Verifica a senha
                elseif (password_verify($password, $userData['password'])) {

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

                    return new RedirectResponse('/usuario');
                } else {
                    $error = "Senha ou login incorretos";
                }
            }
        }

        // 4. ENVIA O ERRO PARA A VIEW
        return new HtmlResponse($this->template->render('app::login', [
            'error' => $error,
        ]));
    }
}
