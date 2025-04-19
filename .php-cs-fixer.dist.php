<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->exclude([
        // Exclude the same directories as in phpcs.xml.dist
        'wp-admin/includes/class-ftp',
        'wp-admin/includes/class-pclzip.php',
        'wp-admin/includes/deprecated.php',
        'wp-admin/includes/ms-deprecated.php',
        'wp-includes/atomlib.php',
        'wp-includes/class-avif-info.php',
        'wp-includes/class-IXR.php',
        'wp-includes/class-json.php',
        'wp-includes/class-phpass.php',
        'wp-includes/class-pop3.php',
        'wp-includes/class-requests.php',
        'wp-includes/class-simplepie.php',
        'wp-includes/class-snoopy.php',
        'wp-includes/deprecated.php',
        'wp-includes/ms-deprecated.php',
        'wp-includes/pluggable-deprecated.php',
        'wp-includes/rss.php',
        'wp-includes/assets',
        'wp-includes/blocks',
        'wp-includes/ID3',
        'wp-includes/IXR',
        'wp-includes/js',
        'wp-includes/PHPMailer',
        'wp-includes/Requests',
        'wp-includes/SimplePie',
        'wp-includes/sodium_compat',
        'wp-includes/Text',
    ])
    ->notPath([
        // Exclude specific files as in phpcs.xml.dist
        'wp-content/advanced-cache.php',
        'wp-content/blog-deleted.php',
        'wp-content/blog-inactive.php',
        'wp-content/blog-suspended.php',
        'wp-content/db-error.php',
        'wp-content/db.php',
        'wp-content/fatal-error-handler.php',
        'wp-content/install.php',
        'wp-content/maintenance.php',
        'wp-content/object-cache.php',
        'wp-content/php-error.php',
        'wp-content/sunrise.php',
    ])
    ->notPath('wp-content/mu-plugins/*')
    ->notPath('wp-content/plugins/*')
    ->notPath('wp-content/themes/(?!twenty)*')
    ->notPath('wp-content/languages/*')
    ->name('*.php');

// Create .cache directory if it doesn't exist
if (!is_dir(__DIR__ . '/.cache') && !mkdir(__DIR__ . '/.cache', 0755, true) && !is_dir(__DIR__ . '/.cache')) {
    throw new \RuntimeException('Failed to create cache directory');
}

return (new PhpCsFixer\Config())
    ->setRules([
        '@PER-CS' => true,
        // Additional rules can be added here if needed
    ])
    ->setCacheFile(__DIR__ . '/.cache/.php-cs-fixer.cache')
    ->setFinder($finder);
