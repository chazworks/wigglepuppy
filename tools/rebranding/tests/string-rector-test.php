<?php
/**
 * Test file for WordPressToWigglePuppyStringRector
 */

// These should be replaced:
$test1 = "This is a WordPress string.";
$test2 = "This is a wordpress string.";
$test3 = "This is a WORDPRESS string.";

// These should not be replaced:
$wordpress_variable = "test";
function wordpress_function() {
    return "test";
}
$test4 = "Visit wordpress.org for more information.";
$test5 = "Copyright (c) WordPress Foundation.";
$test6 = "This is a wordpress.php file.";

// This line has both wordpress and wigglepuppy and should not be changed.
$test7 = "This is a wordpress to wigglepuppy conversion test.";

// Expected results after transformation:
// $test1 = "This is a WigglePuppy string.";
// $test2 = "This is a wigglepuppy string.";
// $test3 = "This is a WIGGLEPUPPY string.";
// All others remain unchanged
