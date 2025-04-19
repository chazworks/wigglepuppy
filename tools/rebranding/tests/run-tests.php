<?php
/**
 * Test script for WordPress to WigglePuppy rebranding
 *
 * This script tests the rebranding process by:
 * 1. Testing regex replacements on non-PHP files
 * 2. Testing WordPressToWigglePuppyStringRector on PHP string literals
 * 3. Testing WordPressToWigglePuppyCommentRector on PHP comments
 * 4. Verifying that the caveats in rebranding.md were followed
 */

// Set up paths
$testsDir = __DIR__;
$toolsDir = dirname($testsDir);
$projectDir = dirname($toolsDir, 2);

// Include necessary files
require_once $projectDir . '/vendor/autoload.php';

// Colors for console output
$green = "\033[32m";
$red = "\033[31m";
$yellow = "\033[33m";
$reset = "\033[0m";

echo "Running WordPress to WigglePuppy rebranding tests...\n\n";

// Function to create a temporary directory
function createTempDir() {
    $tempDir = sys_get_temp_dir() . '/wp-rebranding-tests-' . uniqid('', true);
    if (!mkdir($tempDir, 0777, true) && !is_dir($tempDir)) {
        throw new \RuntimeException(sprintf('Directory "%s" was not created', $tempDir));
    }
    return $tempDir;
}

// Function to copy test files to temporary directory
function copyTestFiles($sourceDir, $tempDir) {
    $files = ['regex-test.txt', 'string-rector-test.php', 'comment-rector-test.php'];
    foreach ($files as $file) {
        copy($sourceDir . '/' . $file, $tempDir . '/' . $file);
    }
}

// Function to run tests
function runTests() {
    global $testsDir, $toolsDir, $projectDir, $green, $red, $yellow, $reset;
    $errors = 0;
    $warnings = 0;

    // Create temporary directory for tests
    $tempDir = createTempDir();
    echo "Created temporary directory for tests: $tempDir\n\n";

    // Copy test files to temporary directory
    copyTestFiles($testsDir, $tempDir);
    echo "Copied test files to temporary directory\n\n";

    // Test 1: Regex replacements on non-PHP files
    echo "Test 1: Regex replacements on non-PHP files\n";
    echo "----------------------------------------\n";

    // Use the temporary file for testing
    $regexTestFile = $tempDir . '/regex-test.txt';

    // Apply regex replacements
    $content = file_get_contents($regexTestFile);
    $lines = explode("\n", $content);
    $newLines = [];

    foreach ($lines as $line) {
        // Skip if the line contains patterns we shouldn't replace
        if (preg_match('/\$.*wordpress|function.*wordpress|wordpress\.org|wordpress\.com|copyright.*wordpress|.*\.wordpress\.|.*wordpress\.php|.*wordpress.*wigglepuppy.*|.*wigglepuppy.*wordpress.*/i', $line)) {
            $newLines[] = $line;
        } else {
            // Replace WordPress with WigglePuppy preserving case
            $line = preg_replace('/\bWordPress\b/', 'WigglePuppy', $line);
            $line = preg_replace('/\bwordpress\b/', 'wigglepuppy', $line);
            $line = preg_replace('/\bWORDPRESS\b/', 'WIGGLEPUPPY', $line);
            $newLines[] = $line;
        }
    }

    $content = implode("\n", $newLines);
    file_put_contents($regexTestFile, $content);

    // Verify the replacements
    $content = file_get_contents($regexTestFile);

    // Check that the first three lines were replaced
    if (strpos($content, 'This is a WigglePuppy test file.') !== false) {
        echo "{$green}✓ 'WordPress' was correctly replaced with 'WigglePuppy'{$reset}\n";
    } else {
        echo "{$red}✗ 'WordPress' was not replaced with 'WigglePuppy'{$reset}\n";
        $errors++;
    }

    if (strpos($content, 'This is a wigglepuppy test file.') !== false) {
        echo "{$green}✓ 'wordpress' was correctly replaced with 'wigglepuppy'{$reset}\n";
    } else {
        echo "{$red}✗ 'wordpress' was not replaced with 'wigglepuppy'{$reset}\n";
        $errors++;
    }

    if (strpos($content, 'This is a WIGGLEPUPPY test file.') !== false) {
        echo "{$green}✓ 'WORDPRESS' was correctly replaced with 'WIGGLEPUPPY'{$reset}\n";
    } else {
        echo "{$red}✗ 'WORDPRESS' was not replaced with 'WIGGLEPUPPY'{$reset}\n";
        $errors++;
    }

    // Check that the caveats were followed
    if (strpos($content, '$wordpress_variable = \'test\';') !== false) {
        echo "{$green}✓ Variable name containing 'wordpress' was not changed{$reset}\n";
    } else {
        echo "{$red}✗ Variable name containing 'wordpress' was incorrectly changed{$reset}\n";
        $errors++;
    }

    if (strpos($content, 'function wordpress_function()') !== false) {
        echo "{$green}✓ Function name containing 'wordpress' was not changed{$reset}\n";
    } else {
        echo "{$red}✗ Function name containing 'wordpress' was incorrectly changed{$reset}\n";
        $errors++;
    }

    if (strpos($content, 'Visit wordpress.org for more information.') !== false) {
        echo "{$green}✓ URL containing 'wordpress.org' was not changed{$reset}\n";
    } else {
        echo "{$red}✗ URL containing 'wordpress.org' was incorrectly changed{$reset}\n";
        $errors++;
    }

    if (strpos($content, 'Copyright (c) WordPress Foundation.') !== false) {
        echo "{$green}✓ Copyright notice containing 'WordPress' was not changed{$reset}\n";
    } else {
        echo "{$red}✗ Copyright notice containing 'WordPress' was incorrectly changed{$reset}\n";
        $errors++;
    }

    if (strpos($content, 'This is a wordpress.php file.') !== false) {
        echo "{$green}✓ Filename containing 'wordpress.php' was not changed{$reset}\n";
    } else {
        echo "{$red}✗ Filename containing 'wordpress.php' was incorrectly changed{$reset}\n";
        $errors++;
    }

    if (strpos($content, 'This is a wordpress to wigglepuppy conversion test.') !== false) {
        echo "{$green}✓ Line containing both 'wordpress' and 'wigglepuppy' was not changed{$reset}\n";
    } else {
        echo "{$red}✗ Line containing both 'wordpress' and 'wigglepuppy' was incorrectly changed{$reset}\n";
        $errors++;
    }

    echo "\n";

    // Test 2: WordPressToWigglePuppyStringRector
    echo "Test 2: WordPressToWigglePuppyStringRector\n";
    echo "----------------------------------------\n";

    // Create a temporary rector config file for testing
    $tempRectorConfig = $tempDir . '/rector.php';
    $rectorConfigContent = <<<'EOD'
<?php
declare(strict_types=1);

use Rector\Config\RectorConfig;
use WigglePuppy\Rector\Rebranding\WordPressToWigglePuppyStringRector;
use WigglePuppy\Rector\Rebranding\WordPressToWigglePuppyCommentRector;

return function (RectorConfig $rectorConfig): void {
    // Register autoloader for custom rules
    $rectorConfig->autoloadPaths([
        __DIR__ . '/../tools/rebranding/rector/src'
    ]);

    // Apply rules
    $rectorConfig->rules([
        WordPressToWigglePuppyStringRector::class,
    ]);
};
EOD;
    file_put_contents($tempRectorConfig, $rectorConfigContent);

    // Run Rector on the string test file
    $stringTestFile = $tempDir . '/string-rector-test.php';
    $originalContent = file_get_contents($stringTestFile);

    // Execute Rector using shell_exec
    $command = "cd $projectDir && vendor/bin/rector process $stringTestFile --config=$tempRectorConfig --dry-run";
    $output = shell_exec($command);

    echo "Rector output for string test:\n";
    echo $output . "\n";

    // Check if the expected changes would be made
    if (strpos($output, 'This is a WigglePuppy string') !== false) {
        echo "{$green}✓ 'WordPress' would be correctly replaced with 'WigglePuppy' in strings{$reset}\n";
    } else {
        echo "{$red}✗ 'WordPress' would not be replaced with 'WigglePuppy' in strings{$reset}\n";
        $errors++;
    }

    echo "\n";

    // Test 3: WordPressToWigglePuppyCommentRector
    echo "Test 3: WordPressToWigglePuppyCommentRector\n";
    echo "----------------------------------------\n";

    // Create a temporary rector config file for testing comments
    $tempRectorConfigComments = $tempDir . '/rector-comments.php';
    $rectorConfigCommentsContent = <<<'EOD'
<?php
declare(strict_types=1);

use Rector\Config\RectorConfig;
use WigglePuppy\Rector\Rebranding\WordPressToWigglePuppyStringRector;
use WigglePuppy\Rector\Rebranding\WordPressToWigglePuppyCommentRector;

return function (RectorConfig $rectorConfig): void {
    // Register autoloader for custom rules
    $rectorConfig->autoloadPaths([
        __DIR__ . '/../tools/rebranding/rector/src'
    ]);

    // Apply rules
    $rectorConfig->rules([
        WordPressToWigglePuppyCommentRector::class,
    ]);
};
EOD;
    file_put_contents($tempRectorConfigComments, $rectorConfigCommentsContent);

    // Run Rector on the comment test file
    $commentTestFile = $tempDir . '/comment-rector-test.php';
    $originalCommentContent = file_get_contents($commentTestFile);

    // Execute Rector using shell_exec
    $command = "cd $projectDir && vendor/bin/rector process $commentTestFile --config=$tempRectorConfigComments --dry-run";
    $output = shell_exec($command);

    echo "Rector output for comment test:\n";
    echo $output . "\n";

    // Check if the expected changes would be made
    if (strpos($output, 'This is a WigglePuppy comment') !== false) {
        echo "{$green}✓ 'WordPress' would be correctly replaced with 'WigglePuppy' in comments{$reset}\n";
    } else {
        echo "{$red}✗ 'WordPress' would not be replaced with 'WigglePuppy' in comments{$reset}\n";
        $errors++;
    }

    echo "\n";

    // Clean up temporary directory
    array_map('unlink', glob("$tempDir/*"));
    rmdir($tempDir);
    echo "Cleaned up temporary directory\n\n";

    // Summary
    echo "Test Summary\n";
    echo "------------\n";
    if ($errors === 0) {
        echo "{$green}All tests passed!{$reset}\n";
    } else {
        echo "{$red}$errors error(s) found.{$reset}\n";
    }

    return $errors === 0;
}

// Run the tests
$success = runTests();

// Exit with appropriate status code
exit($success ? 0 : 1);
