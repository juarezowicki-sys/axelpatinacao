<?php

declare(strict_types=1);

return [
    // Provides application-wide services.
    // We recommend using fully-qualified class names whenever possible as
    // service names.
    'dependencies' => [
        // Use 'aliases' to alias a service name to another service. The
        // key is the alias name, the value is the service to which it points.
        'aliases' => [
            // Fully\Qualified\ClassOrInterfaceName::class => Fully\Qualified\ClassName::class,
        ],
        // Use 'invokables' for constructor-less services, or services that do
        // not require arguments to the constructor. Map a service name to the
        // class name.
        'invokables' => [
            // Fully\Qualified\InterfaceName::class => Fully\Qualified\ClassName::class,
        ],
        // Use 'factories' for services provided by callbacks/factory classes.
        'factories' => [

            \Mezzio\Authentication\UserInterface::class => \Mezzio\Authentication\DefaultUserFactory::class,
            // Esta factory ensina o Mezzio a "fabricar" o motor do RBAC
            \Mezzio\Authorization\Rbac\LaminasRbac::class => \Mezzio\Authorization\Rbac\LaminasRbacFactory::class,

            // ESTAS DUAS SÃO OBRIGATÓRIAS PARA O LOGIN FUNCIONAR:
            \Mezzio\Authentication\AuthenticationMiddleware::class => \Mezzio\Authentication\AuthenticationMiddlewareFactory::class,
            \Mezzio\Authentication\AuthenticationInterface::class  => \Mezzio\Authentication\Session\PhpSessionFactory::class,

            // SE ESTIVER USANDO RBAC, ADICIONE ESTA TAMBÉM:
            \Mezzio\Authorization\AuthorizationMiddleware::class   => \Mezzio\Authorization\AuthorizationMiddlewareFactory::class,
            \Mezzio\Authentication\Session\PhpSession::class => \Mezzio\Authentication\Session\PhpSessionFactory::class,
            // Esta linha diz ao Mezzio como criar o objeto do Usuário após o login:
            \Mezzio\Authentication\UserInterface::class => \Mezzio\Authentication\DefaultUserFactory::class,
            \Mezzio\Session\SessionMiddleware::class => \Mezzio\Session\SessionMiddlewareFactory::class,
        ],

    ]

];
