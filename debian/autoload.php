<?php

declare(strict_types=1);

require_once '/usr/share/php/Composer/InstalledVersions.php';
require_once '/usr/share/php/Ease/autoload.php';
require_once '/usr/share/php/EaseTWB5/autoload.php';
require_once '/usr/share/php/EaseHtml/autoload.php';
require_once '/usr/share/php/EaseHtmlWidgets/autoload.php';
require_once '/usr/share/php/EaseFluentPDO/autoload.php';
require_once '/usr/share/php/GuzzleHttp/autoload.php';
require_once '/usr/share/php/Psr/Http/Message/autoload.php';
require_once '/usr/share/php/Psr/Http/Message/factory-autoload.php';

// Bundled vendor helpers (no Debian package available)
require_once '/usr/lib/csas-authorize/vendor/getallheaders.php';
require_once '/usr/lib/csas-authorize/vendor/kint_init.php';

// System CSasAccounts library (SpojeNet\CSas\Accounts\*, SpojeNet\CSas\Modes\*, etc.)
spl_autoload_register(function (string $class): void {
    $prefix = 'SpojeNet\\CSas\\';
    if (str_starts_with($class, $prefix)) {
        $file = '/usr/share/php/CSasAccounts/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});

// Project's own CSas application classes
spl_autoload_register(function (string $class): void {
    $prefix = 'SpojeNet\\CSas\\';
    if (str_starts_with($class, $prefix)) {
        $file = '/usr/lib/csas-authorize/CSas/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});

// Bundled vendor packages (no Debian equivalent)
spl_autoload_register(function (string $class): void {
    $map = [
        'League\\OAuth2\\Client\\' => '/usr/lib/csas-authorize/vendor/League/OAuth2/Client/',
        'Kint\\'                   => '/usr/lib/csas-authorize/vendor/Kint/',
    ];
    foreach ($map as $prefix => $base) {
        if (str_starts_with($class, $prefix)) {
            $file = $base . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (file_exists($file)) {
                require $file;
            }
            return;
        }
    }
});

(function (): void {
    $versions = [];
    foreach (\Composer\InstalledVersions::getAllRawData() as $d) {
        $versions = array_merge($versions, $d['versions'] ?? []);
    }
    $name    = 'unknown';
    $version = '0.0.0';
    $versions[$name] = ['pretty_version' => $version, 'version' => $version,
        'reference' => null, 'type' => 'library', 'install_path' => __DIR__,
        'aliases' => [], 'dev_requirement' => false];
    \Composer\InstalledVersions::reload([
        'root' => ['name' => $name, 'pretty_version' => $version, 'version' => $version,
            'reference' => null, 'type' => 'library', 'install_path' => __DIR__,
            'aliases' => [], 'dev' => false],
        'versions' => $versions,
    ]);
})();
