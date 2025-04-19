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

// Whitespace preservation tests:
$test8 = "This    is    a    WordPress    test    with    multiple    spaces.";
$test9 = "This	is	a	WordPress	test	with	tabs.";
$test10 = "  This is a WordPress test with leading spaces.";
$test11 = "		This is a WordPress test with leading tabs.";
$test12 = "This is a WordPress test with trailing spaces.    ";
$test13 = "This is a WordPress test with trailing tabs.		";
$test14 = "  This    is    a    WordPress    test    with    mixed    whitespace.		";

// Expected results after transformation:
// $test1 = "This is a WigglePuppy string.";
// $test2 = "This is a wigglepuppy string.";
// $test3 = "This is a WIGGLEPUPPY string.";
// $test8 = "This    is    a    WigglePuppy    test    with    multiple    spaces.";
// $test9 = "This	is	a	WigglePuppy	test	with	tabs.";
// $test10 = "  This is a WigglePuppy test with leading spaces.";
// $test11 = "		This is a WigglePuppy test with leading tabs.";
// $test12 = "This is a WigglePuppy test with trailing spaces.    ";
// $test13 = "This is a WigglePuppy test with trailing tabs.		";
// $test14 = "  This    is    a    WigglePuppy    test    with    mixed    whitespace.		";
// All others remain unchanged
