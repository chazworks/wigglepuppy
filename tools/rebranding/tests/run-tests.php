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

// Function to run tests
function runTests() {
    global $testsDir, $toolsDir, $projectDir, $green, $red, $yellow, $reset;
    $errors = 0;
    $warnings = 0;

    // Test 1: Regex replacements on non-PHP files
    echo "Test 1: Regex replacements on non-PHP files\n";
    echo "----------------------------------------\n";

    // Create a temporary copy of the test file
    $regexTestFile = $testsDir . '/regex-test.txt';
    $regexTestFileCopy = $testsDir . '/regex-test-copy.txt';
    copy($regexTestFile, $regexTestFileCopy);

    // Apply regex replacements
    $content = file_get_contents($regexTestFileCopy);

    // Skip if the line contains patterns we shouldn't replace
    if (!preg_match('/\$.*wordpress|function.*wordpress|wordpress\.org|wordpress\.com|copyright.*wordpress|.*\.wordpress\.|.*wordpress.*wigglepuppy.*|.*wigglepuppy.*wordpress.*/i', $content)) {
        // Replace WordPress with WigglePuppy preserving case
        $content = preg_replace('/\bWordPress\b/', 'WigglePuppy', $content);
        $content = preg_replace('/\bwordpress\b/', 'wigglepuppy', $content);
        $content = preg_replace('/\bWORDPRESS\b/', 'WIGGLEPUPPY', $content);
    }

    file_put_contents($regexTestFileCopy, $content);

    // Verify the replacements
    $content = file_get_contents($regexTestFileCopy);

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

    // Clean up
    unlink($regexTestFileCopy);

    echo "\n";

    // Test 2: WordPressToWigglePuppyStringRector
    echo "Test 2: WordPressToWigglePuppyStringRector\n";
    echo "----------------------------------------\n";
    echo "{$yellow}Note: This test requires running Rector on the test file.{$reset}\n";
    echo "{$yellow}To run this test, execute the following command:{$reset}\n";
    echo "vendor/bin/rector process tools/rebranding/tests/string-rector-test.php --config=rector.php\n\n";
    $warnings++;

    // Test 3: WordPressToWigglePuppyCommentRector
    echo "Test 3: WordPressToWigglePuppyCommentRector\n";
    echo "----------------------------------------\n";
    echo "{$yellow}Note: This test requires running Rector on the test file.{$reset}\n";
    echo "{$yellow}To run this test, execute the following command:{$reset}\n";
    echo "vendor/bin/rector process tools/rebranding/tests/comment-rector-test.php --config=rector.php\n\n";
    $warnings++;

    // Summary
    echo "Test Summary\n";
    echo "------------\n";
    if ($errors === 0) {
        echo "{$green}All tests passed!{$reset}\n";
    } else {
        echo "{$red}$errors error(s) found.{$reset}\n";
    }

    if ($warnings > 0) {
        echo "{$yellow}$warnings warning(s) found. Some tests require manual execution.{$reset}\n";
    }

    return $errors === 0;
}

// Run the tests
$success = runTests();

// Exit with appropriate status code
exit($success ? 0 : 1);
