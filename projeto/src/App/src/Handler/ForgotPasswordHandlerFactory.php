<?php
namespace App\Handler;

use Psr\Container\ContainerInterface;
use Mezzio\Template\TemplateRendererInterface;
use Laminas\Db\Adapter\AdapterInterface;

class ForgotPasswordHandlerFactory
{
    public function __invoke(ContainerInterface $container): ForgotPasswordHandler
    {
        $config = $container->get('config');
        return new ForgotPasswordHandler(
            $container->get(TemplateRendererInterface::class),
            $container->get(AdapterInterface::class),
            $config['mail_config'] // Pega aquela config do Gmail que fizemos
        );
    }
}