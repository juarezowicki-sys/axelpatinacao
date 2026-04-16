<?php

declare(strict_types=1);

namespace App;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.laminas.dev/laminas-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates'    => $this->getTemplates(),
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies(): array
    {
        return [
            'invokables' => [
                Handler\PingHandler::class => Handler\PingHandler::class,
            ],
            'aliases' => [
                // Isso faz o Mezzio usar sua classe quando alguém pedir a Interface oficial
                \Mezzio\Authentication\UserRepositoryInterface::class => Authentication\MySqlUserRepository::class,
                \Mezzio\Authorization\AuthorizationInterface::class => \Mezzio\Authorization\Rbac\LaminasRbac::class,
            ],
            'factories'  => [
                Handler\AtletaHandler::class => Handler\AtletaHandlerFactory::class,
                Handler\ConfirmacaoHandler::class => Handler\ConfirmacaoHandlerFactory::class,
                Handler\EditarAtletaHandler::class => Handler\EditarAtletaHandlerFactory::class,
                Handler\EditarAulaHandler::class => Handler\EditarAulaHandlerFactory::class,
                Handler\EditarTurmaHandler::class  => Handler\EditarTurmaHandlerFactory::class,
                Handler\EditarUsuarioHandler::class => Handler\EditarUsuarioHandlerFactory::class,
                Handler\ExcluirAtletaHandler::class => Handler\ExcluirAtletaHandlerFactory::class,
                Handler\ExcluirAulaHandler::class => Handler\ExcluirAulaHandlerFactory::class,
                Handler\HomeAulaHandler::class => Handler\HomeAulaHandlerFactory::class,
                Handler\ExcluirTurmaHandler::class  => Handler\ExcluirTurmaHandlerFactory::class,
                Handler\ExcluirUsuarioHandler::class => Handler\ExcluirUsuarioHandlerFactory::class,
                // Handlers de Recuperação de Senha
                Handler\ForgotPasswordHandler::class => Handler\ForgotPasswordHandlerFactory::class,
                Handler\GoogleAuthHandler::class => Handler\GoogleAuthHandlerFactory::class,

                Handler\HomePageHandler::class => Handler\HomePageHandlerFactory::class,
                Handler\HomeUsuarioHandler::class => Handler\HomeUsuarioHandlerFactory::class,

                Handler\ListarAtletasHandler::class => Handler\ListarAtletasHandlerFactory::class,
                Handler\ListarAulasHandler::class => Handler\ListarAulasHandlerFactory::class,
                Handler\ListarTurmasHandler::class  => Handler\ListarTurmasHandlerFactory::class,
                Handler\ListarUsuariosHandler::class => Handler\ListarUsuariosHandlerFactory::class,
                Handler\LoginHandler::class => Handler\LoginHandlerFactory::class,
                Handler\LogoutHandler::class => Handler\LogoutHandlerFactory::class,
                Handler\ChamadaHandler::class => Handler\ChamadaHandlerFactory::class,
                Handler\ResetPasswordHandler::class    => Handler\ResetPasswordHandlerFactory::class,

                Handler\TurmaHandler::class => Handler\TurmaHandlerFactory::class,
                Handler\UsuarioHandler::class => Handler\UsuarioHandlerFactory::class,

                Authentication\MySqlUserRepository::class => Authentication\MySqlUserRepositoryFactory::class,
                Middleware\AuthorizationErrorMiddleware::class => Middleware\AuthorizationErrorMiddlewareFactory::class,
                Middleware\TemplateDefaultsMiddleware::class => Middleware\TemplateDefaultsMiddlewareFactory::class,
                Service\MailService::class => Service\MailServiceFactory::class,

            ],
        ];
    }

    /**
     * Returns the templates configuration
     */
    public function getTemplates(): array
    {
        return [
            'paths' => [
                'app'    => [__DIR__ . '/templates/app'],
                'error'  => [__DIR__ . '/templates/error'],
                'layout' => [__DIR__ . '/templates/layout'],
            ],
        ];
    }
}
