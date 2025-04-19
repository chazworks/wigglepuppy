<?php
/**
 * Test file for WordPressToWigglePuppyCommentRector
 *
 * This is a WordPress comment that should be replaced.
 * This is a wordpress comment that should be replaced.
 * This is a WORDPRESS comment that should be replaced.
 */

// These comments should be replaced:
// This is a WordPress comment.
// This is a wordpress comment.
// This is a WORDPRESS comment.

function test_function() {
    // These comments should not be replaced:
    // $wordpress_variable = 'test';
    // function wordpress_function() {}
    // Visit wordpress.org for more information.
    // Copyright (c) WordPress Foundation.
    // This is a wordpress.php file.

    return true;
}

/**
 * This comment has both wordpress and wigglepuppy and should not be changed.
 * This is a wordpress to wigglepuppy conversion test.
 */

// Whitespace preservation tests:
// This    is    a    WordPress    comment    with    multiple    spaces.
// This	is	a	WordPress	comment	with	tabs.
//   This is a WordPress comment with leading spaces.
//		This is a WordPress comment with leading tabs.
// This is a WordPress comment with trailing spaces.
// This is a WordPress comment with trailing tabs.
//   This    is    a    WordPress    comment    with    mixed    whitespace.

/**
 * Whitespace preservation tests in docblocks:
 *
 * This    is    a    WordPress    comment    with    multiple    spaces.
 * This	is	a	WordPress	comment	with	tabs.
 *   This is a WordPress comment with leading spaces.
 *		This is a WordPress comment with leading tabs.
 * This is a WordPress comment with trailing spaces.
 * This is a WordPress comment with trailing tabs.
 *   This    is    a    WordPress    comment    with    mixed    whitespace.
 */

// Expected results after transformation:
// All WordPress/wordpress/WORDPRESS in comments should be replaced with
// WigglePuppy/wigglepuppy/WIGGLEPUPPY except for the cases mentioned in the caveats.
// All whitespace should be preserved exactly as it appears in the original comments.
