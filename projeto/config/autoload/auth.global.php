<?php

return [
    'authentication' => [
        'username' => 'email',
        'password' => 'password',
        'redirect' => '/login', // MANTENHA AQUI para não dar o erro de ServiceNotCreated
        'timeout' => 1440, // Tempo em segundos. Se estiver baixo, o login cai.
    ],
    'mezzio-authentication' => [
        'unauthenticated_user_factory' => \Mezzio\Authentication\DefaultUserFactory::class,
    ],
    'dependencies' => [
        'aliases' => [
            \Mezzio\Authentication\UserInterface::class => \App\Authentication\User::class,
        ],
        'factories' => [
            \App\Authentication\User::class => \Mezzio\Authentication\DefaultUserFactory::class,
        ],
    ],
];
