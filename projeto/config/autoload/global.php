<?php

declare(strict_types=1);

return [
    'translator' => [
        'locale' => 'pt_BR',
        'translation_file_patterns' => [
            [
                'type'     => 'phparray',
                'base_dir' => getcwd() . '/vendor/laminas/laminas-i18n-resources/languages',
                'pattern'  => '%s/Laminas_Validate.php',
            ],
        ],
    ],
    'session' => [
        'persistence' => [
            'ext' => [
                // Configurações da extensão nativa do PHP
                'cache_limiter' => 'public', // Mude de 'nocache' para 'public' para ajudar na persistência
                'cookie_samesite' => 'Lax',

                // --- ADICIONE ESTAS DUAS LINHAS ABAIXO ---
                'cookie_lifetime' => 86400, // 24 horas em segundos
                'cache_expire'    => 1440,  // 24 horas em minutos (usado pelo motor do Mezzio)
                // ----------------------------------------

                'delete_cookie_on_empty_session' => true,
            ],
        ],
    ],
];
