<?php

declare(strict_types=1);

namespace App\Service;

use Laminas\Mail\Message;
use Laminas\Mail\Transport\Smtp as SmtpTransport;
use Laminas\Mail\Transport\SmtpOptions;
use Laminas\Mime\Message as MimeMessage;
use Laminas\Mime\Part as MimePart;

class MailService
{
    private array $config;

    // O Mezzio vai injetar o array 'mail_config' aqui
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function sendConfirmation(string $toEmail, string $link): void
    {
        $transport = new SmtpTransport();
        $options   = new SmtpOptions($this->config);
        $transport->setOptions($options);

        // Limpa possíveis espaços em branco no e-mail do usuário
        $toEmail = trim($toEmail);
        // Pega o e-mail do remetente direto do seu mail.local.php
        $fromEmail = trim($this->config['connection_config']['username']);

        $html = new MimePart("
        <h1>Ative sua conta</h1>
        <p>Clique no link abaixo para confirmar seu cadastro:</p>
        <p><a href='$link'>Confirmar Cadastro</a></p>
        <br><small>Se o botão não funcionar, copie e cole este link: $link</small>
    ");
        $html->type = "text/html";
        $html->charset = 'utf-16'; // Ajuda com caracteres especiais se houver

        $body = new MimeMessage();
        $body->setParts([$html]);

        $message = new Message();
        $message->setEncoding('utf-8'); // Força a codificação correta do cabeçalho
        $message->addTo($toEmail)
            ->addFrom($fromEmail, 'Axel Patinação')
            ->setSubject('Confirmação de Cadastro - Axel Patinação')
            ->setBody($body);

        $transport->send($message);
    }
}
