<?php

declare(strict_types=1);

use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;


// To enable or disable caching, set the `ConfigAggregator::ENABLE_CACHE` boolean in
// `config/autoload/local.php`.
$cacheConfig = [
    'config_cache_path' => 'data/cache/config-cache.php',
];

$aggregator = new ConfigAggregator([
    \Mezzio\Authentication\Session\ConfigProvider::class,
    \Mezzio\Authentication\ConfigProvider::class,
    \Laminas\Paginator\Adapter\LaminasDb\ConfigProvider::class,
    \Laminas\Paginator\ConfigProvider::class,
    \Mezzio\Flash\ConfigProvider::class,
    \Laminas\Mail\ConfigProvider::class,
    \Mezzio\Authorization\Rbac\ConfigProvider::class,
    \Mezzio\Authorization\ConfigProvider::class,
    \Laminas\Session\ConfigProvider::class,
    \Mezzio\Session\Ext\ConfigProvider::class,
    \Mezzio\Session\ConfigProvider::class,
    \Laminas\Db\ConfigProvider::class,
    \Laminas\Mvc\I18n\ConfigProvider::class,
    \Laminas\Router\ConfigProvider::class,
    \Laminas\I18n\ConfigProvider::class,
    \Mezzio\LaminasView\ConfigProvider::class,
    \Laminas\Form\ConfigProvider::class,
    \Laminas\Hydrator\ConfigProvider::class,
    \Laminas\InputFilter\ConfigProvider::class,
    \Laminas\Filter\ConfigProvider::class,
    \Laminas\Validator\ConfigProvider::class,
    //\Mezzio\Tooling\ConfigProvider::class,
    \Mezzio\Router\FastRouteRouter\ConfigProvider::class,
    \Laminas\HttpHandlerRunner\ConfigProvider::class,
    // Include cache configuration
    new ArrayProvider($cacheConfig),
    \Mezzio\Helper\ConfigProvider::class,
    \Mezzio\ConfigProvider::class,
    \Mezzio\Router\ConfigProvider::class,
    \Laminas\Diactoros\ConfigProvider::class,

    // Swoole config to overwrite some services (if installed)
    // Use a string entre aspas para a IDE parar de procurar a classe inexistente
    class_exists('Mezzio\Swoole\ConfigProvider')
        ? 'Mezzio\Swoole\ConfigProvider'
        : function (): array {
            return [];
        },

    // Default App module config
    App\ConfigProvider::class,

    // Load application config in a pre-defined order in such a way that local settings
    // overwrite global settings. (Loaded as first to last):
    //   - `global.php`
    //   - `*.global.php`
    //   - `local.php`
    //   - `*.local.php`
    new PhpFileProvider(realpath(__DIR__) . '/autoload/{{,*.}global,{,*.}local}.php'),

    // Load development config if it exists
    new PhpFileProvider(realpath(__DIR__) . '/development.config.php'),
], $cacheConfig['config_cache_path']);

return $aggregator->getMergedConfig();
