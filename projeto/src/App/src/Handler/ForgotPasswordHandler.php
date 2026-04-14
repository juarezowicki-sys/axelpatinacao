<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Sql;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\Smtp as SmtpTransport;
use Laminas\Mail\Transport\SmtpOptions;

class ForgotPasswordHandler implements RequestHandlerInterface
{
    public function __construct(
        private TemplateRendererInterface $renderer,
        private AdapterInterface $db,
        private array $mailConfig
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            $params = $request->getParsedBody();
            $email = $params['email'] ?? '';

            $sql = new Sql($this->db);
            $select = $sql->select('usuarios')->where(['email' => $email]);
            $user = $sql->prepareStatementForSqlObject($select)->execute()->current();

            if ($user) {
                $token = bin2hex(random_bytes(20));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $update = $sql->update('usuarios')
                    ->set(['password_reset_token' => $token, 'token_expiry' => $expiry])
                    ->where(['email' => $email]);
                $sql->prepareStatementForSqlObject($update)->execute();

                $this->sendResetEmail($email, $token, $request);
            } else {
                $mensagem = ' -> O e-mail <strong>' . $email . '</strong> não consta em nossa base de dados.<p>-> Se foi um erro de digitação informe o e-mail correto.<p>-> Se não foi um erro de digitação, utilize o nosso link <strong>Fazer meu cadastro</strong>, no menu acima, para fazer o seu cadastro conosco.<p>-> Ou você pode solicitar a substituição do seu e-mail para a administração da escola pelo link do whatsApp abaixo: 
                <a class="text-sm text-blue-600 flex justify-center mt-3" href="https://wa.me/5551993340678?text=Precisamos+conversar+sobre+alterar+o+meu+e-mail+cadastrado+no+site+da+Axel+%21"><img class="h-5 mr-2" src="/img/whats.png" alt="whatsApp logo" />Dúvidas? consulte por whatsApp</a>';
                return new HtmlResponse($this->renderer->render('app::forgot-password', [
                    'message' => $mensagem
                ]));
            }
            return new HtmlResponse($this->renderer->render('app::forgot-password-success'));
        }
        return new HtmlResponse($this->renderer->render('app::forgot-password'));
    }

    private function sendResetEmail(string $email, string $token, ServerRequestInterface $request): void
    {
        $uri = $request->getUri();
        $basePath = $uri->getScheme() . '://' . $uri->getHost();
        // $folderPath = str_replace('/esqueci-senha', '', $uri->getPath());
        //  $link = $basePath . $folderPath . "/resetar-senha?token=" . $token;

        $link = $basePath . "/alterar_a_minha_senha?token=" . $token;

        $bodyMessage = new \Laminas\Mime\Part("Para redefinir sua senha na Axel Patinação, acesse: " . $link);
        $bodyMessage->type = 'text/plain';
        $bodyMessage->charset = 'utf-8';
        $bodyPart = new \Laminas\Mime\Message();
        $bodyPart->setParts([$bodyMessage]);

        $message = new Message();
        $message->setEncoding('UTF-8');
        $message->addTo($email)
            ->addFrom('contato@axelpatinacao.com.br', 'Axel Patinação')
            ->setSubject('Recuperação de Senha')
            ->setBody($bodyPart);

        $transport = new SmtpTransport(new SmtpOptions($this->mailConfig));
        $transport->send($message);
    }
}
