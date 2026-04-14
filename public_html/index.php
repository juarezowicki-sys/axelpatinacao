<?php
declare(strict_types=1);

// 1. Caminho para a pasta projeto (que está um nível acima da public_html)
$rootPath = dirname(__DIR__) . '/projeto';

// 2. Configura as sessões na pasta privada
$caminhoSessoes = $rootPath . '/sessoes';
if (!is_dir($caminhoSessoes)) {
    mkdir($caminhoSessoes, 0777, true);
}

ini_set('session.save_path', $caminhoSessoes);
ini_set('session.gc_maxlifetime', '86400');
ini_set('session.cookie_lifetime', '86400');
ini_set('session.cookie_samesite', 'Lax');

// 3. Entra na pasta projeto e carrega o sistema
chdir($rootPath);
require $rootPath . '/vendor/autoload.php';


(function () {
    /** @var \Psr\Container\ContainerInterface $container */
    $container = require 'config/container.php';

if ($container->has(\Laminas\I18n\Translator\TranslatorInterface::class)) {
    $i18nTranslator = $container->get(\Laminas\I18n\Translator\TranslatorInterface::class);
    
    // Criamos o adaptador que o Validator exige
    $validatorTranslator = new \Laminas\Validator\Translator\Translator($i18nTranslator);
    
    // Agora passamos o adaptador correto
    \Laminas\Validator\AbstractValidator::setDefaultTranslator($validatorTranslator);
}

    /** @var \Mezzio\Application $app */
    $app = $container->get(\Mezzio\Application::class);
    $factory = $container->get(\Mezzio\MiddlewareFactory::class);

    // Executar pipeline e roteamento de middleware programático/declarativo
    // instruções de configuração
    (require 'config/pipeline.php')($app, $factory, $container);
    (require 'config/routes.php')($app, $factory, $container);

    $app->run();
})();
