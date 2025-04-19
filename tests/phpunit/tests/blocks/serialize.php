<?php

/**
 * Tests for block serialization functions.
 *
 * @package WordPress
 * @subpackage Blocks
 *
 * @since 5.3.3
 *
 * @group blocks
 */
class Tests_Blocks_Serialize extends WP_UnitTestCase
{
    /**
     * @dataProvider data_serialize_identity_from_parsed
     *
     * @param string $original Original block markup.
     */
    public function test_serialize_identity_from_parsed($original)
    {
        $blocks = parse_blocks($original);

        $actual = serialize_blocks($blocks);

        $this->assertSame($original, $actual);
    }

    public function data_serialize_identity_from_parsed()
    {
        return [
            // Void block.
            [ '<!-- wp:void /-->' ],

            // Freeform content ($block_name = null).
            [ 'Example.' ],

            // Block with content.
            [ '<!-- wp:content -->Example.<!-- /wp:content -->' ],

            // Block with attributes.
            [ '<!-- wp:attributes {"key":"value"} /-->' ],

            // Block with inner blocks.
            [ "<!-- wp:outer --><!-- wp:inner {\"key\":\"value\"} -->Example.<!-- /wp:inner -->\n\nExample.\n\n<!-- wp:void /--><!-- /wp:outer -->" ],

            // Block with attribute values that may conflict with HTML comment.
            [ '<!-- wp:attributes {"key":"\\u002d\\u002d\\u003c\\u003e\\u0026\\u0022"} /-->' ],

            // Block with attribute values that should not be escaped.
            [ '<!-- wp:attributes {"key":"€1.00 / 3 for €2.00"} /-->' ],
        ];
    }

    public function test_serialized_block_name()
    {
        $this->assertNull(strip_core_block_namespace(null));
        $this->assertSame('example', strip_core_block_namespace('example'));
        $this->assertSame('example', strip_core_block_namespace('core/example'));
        $this->assertSame('plugin/example', strip_core_block_namespace('plugin/example'));
    }

    /**
     * @ticket 59327
     * @ticket 59412
     *
     * @covers ::traverse_and_serialize_blocks
     */
    public function test_traverse_and_serialize_blocks_pre_callback_modifies_current_block()
    {
        $markup = "<!-- wp:outer --><!-- wp:inner {\"key\":\"value\"} -->Example.<!-- /wp:inner -->\n\nExample.\n\n<!-- wp:void /--><!-- /wp:outer -->";
        $blocks = parse_blocks($markup);

        $actual = traverse_and_serialize_blocks($blocks, [ __CLASS__, 'add_attribute_to_inner_block' ]);

        $this->assertSame(
            "<!-- wp:outer --><!-- wp:inner {\"key\":\"value\",\"myattr\":\"myvalue\"} -->Example.<!-- /wp:inner -->\n\nExample.\n\n<!-- wp:void /--><!-- /wp:outer -->",
            $actual,
        );
    }

    /**
     * @ticket 59669
     *
     * @covers ::traverse_and_serialize_blocks
     */
    public function test_traverse_and_serialize_blocks_post_callback_modifies_current_block()
    {
        $markup = "<!-- wp:outer --><!-- wp:inner {\"key\":\"value\"} -->Example.<!-- /wp:inner -->\n\nExample.\n\n<!-- wp:void /--><!-- /wp:outer -->";
        $blocks = parse_blocks($markup);

        $actual = traverse_and_serialize_blocks($blocks, null, [ __CLASS__, 'add_attribute_to_inner_block' ]);

        $this->assertSame(
            "<!-- wp:outer --><!-- wp:inner {\"key\":\"value\",\"myattr\":\"myvalue\"} -->Example.<!-- /wp:inner -->\n\nExample.\n\n<!-- wp:void /--><!-- /wp:outer -->",
            $actual,
        );
    }

    public static function add_attribute_to_inner_block(&$block)
    {
        if ('core/inner' === $block['blockName']) {
            $block['attrs']['myattr'] = 'myvalue';
        }
    }

    /**
     * @ticket 59313
     *
     * @covers ::traverse_and_serialize_blocks
     */
    public function test_traverse_and_serialize_blocks_pre_callback_prepends_to_inner_block()
    {
        $markup = "<!-- wp:outer --><!-- wp:inner {\"key\":\"value\"} -->Example.<!-- /wp:inner -->\n\nExample.\n\n<!-- wp:void /--><!-- /wp:outer -->";
        $blocks = parse_blocks($markup);

        $actual = traverse_and_serialize_blocks($blocks, [ __CLASS__, 'insert_next_to_inner_block_callback' ]);

        $this->assertSame(
            "<!-- wp:outer --><!-- wp:tests/inserted-block /--><!-- wp:inner {\"key\":\"value\"} -->Example.<!-- /wp:inner -->\n\nExample.\n\n<!-- wp:void /--><!-- /wp:outer -->",
            $actual,
        );
    }

    /**
     * @ticket 59313
     *
     * @covers ::traverse_and_serialize_blocks
     */
    public function test_traverse_and_serialize_blocks_post_callback_appends_to_inner_block()
    {
        $markup = "<!-- wp:outer --><!-- wp:inner {\"key\":\"value\"} -->Example.<!-- /wp:inner -->\n\nExample.\n\n<!-- wp:void /--><!-- /wp:outer -->";
        $blocks = parse_blocks($markup);

        $actual = traverse_and_serialize_blocks($blocks, null, [ __CLASS__, 'insert_next_to_inner_block_callback' ]);

        $this->assertSame(
            "<!-- wp:outer --><!-- wp:inner {\"key\":\"value\"} -->Example.<!-- /wp:inner --><!-- wp:tests/inserted-block /-->\n\nExample.\n\n<!-- wp:void /--><!-- /wp:outer -->",
            $actual,
        );
    }

    public static function insert_next_to_inner_block_callback($block)
    {
        if ('core/inner' !== $block['blockName']) {
            return '';
        }

        return get_comment_delimited_block_content('tests/inserted-block', [], '');
    }

    /**
     * @ticket 59313
     *
     * @covers ::traverse_and_serialize_blocks
     */
    public function test_traverse_and_serialize_blocks_pre_callback_prepends_to_child_blocks()
    {
        $markup = "<!-- wp:outer --><!-- wp:inner {\"key\":\"value\"} -->Example.<!-- /wp:inner -->\n\nExample.\n\n<!-- wp:void /--><!-- /wp:outer -->";
        $blocks = parse_blocks($markup);

        $actual = traverse_and_serialize_blocks($blocks, [ __CLASS__, 'insert_next_to_child_blocks_callback' ]);

        $this->assertSame(
            "<!-- wp:outer --><!-- wp:tests/inserted-block {\"parent\":\"core/outer\"} /--><!-- wp:inner {\"key\":\"value\"} -->Example.<!-- /wp:inner -->\n\nExample.\n\n<!-- wp:tests/inserted-block {\"parent\":\"core/outer\"} /--><!-- wp:void /--><!-- /wp:outer -->",
            $actual,
        );
    }

    /**
     * @ticket 59313
     *
     * @covers ::traverse_and_serialize_blocks
     */
    public function test_traverse_and_serialize_blocks_post_callback_appends_to_child_blocks()
    {
        $markup = "<!-- wp:outer --><!-- wp:inner {\"key\":\"value\"} -->Example.<!-- /wp:inner -->\n\nExample.\n\n<!-- wp:void /--><!-- /wp:outer -->";
        $blocks = parse_blocks($markup);

        $actual = traverse_and_serialize_blocks($blocks, null, [ __CLASS__, 'insert_next_to_child_blocks_callback' ]);

        $this->assertSame(
            "<!-- wp:outer --><!-- wp:inner {\"key\":\"value\"} -->Example.<!-- /wp:inner --><!-- wp:tests/inserted-block {\"parent\":\"core/outer\"} /-->\n\nExample.\n\n<!-- wp:void /--><!-- wp:tests/inserted-block {\"parent\":\"core/outer\"} /--><!-- /wp:outer -->",
            $actual,
        );
    }

    public static function insert_next_to_child_blocks_callback($block, $parent_block)
    {
        if (! isset($parent_block)) {
            return '';
        }

        return get_comment_delimited_block_content(
            'tests/inserted-block',
            [
                'parent' => $parent_block['blockName'],
            ],
            '',
        );
    }

    /**
     * @ticket 59313
     *
     * @covers ::traverse_and_serialize_blocks
     */
    public function test_traverse_and_serialize_blocks_pre_callback_prepends_if_prev_block()
    {
        $markup = "<!-- wp:outer --><!-- wp:inner {\"key\":\"value\"} -->Example.<!-- /wp:inner -->\n\nExample.\n\n<!-- wp:void /--><!-- /wp:outer -->";
        $blocks = parse_blocks($markup);

        $actual = traverse_and_serialize_blocks($blocks, [ __CLASS__, 'insert_next_to_if_prev_or_next_block_callback' ]);

        $this->assertSame(
            "<!-- wp:outer --><!-- wp:inner {\"key\":\"value\"} -->Example.<!-- /wp:inner -->\n\nExample.\n\n<!-- wp:tests/inserted-block {\"prev_or_next\":\"core/inner\"} /--><!-- wp:void /--><!-- /wp:outer -->",
            $actual,
        );
    }

    /**
     * @ticket 59313
     *
     * @covers ::traverse_and_serialize_blocks
     */
    public function test_traverse_and_serialize_blocks_post_callback_appends_if_prev_block()
    {
        $markup = "<!-- wp:outer --><!-- wp:inner {\"key\":\"value\"} -->Example.<!-- /wp:inner -->\n\nExample.\n\n<!-- wp:void /--><!-- /wp:outer -->";
        $blocks = parse_blocks($markup);

        $actual = traverse_and_serialize_blocks($blocks, null, [ __CLASS__, 'insert_next_to_if_prev_or_next_block_callback' ]);

        $this->assertSame(
            "<!-- wp:outer --><!-- wp:inner {\"key\":\"value\"} -->Example.<!-- /wp:inner --><!-- wp:tests/inserted-block {\"prev_or_next\":\"core/void\"} /-->\n\nExample.\n\n<!-- wp:void /--><!-- /wp:outer -->",
            $actual,
        );
    }

    public static function insert_next_to_if_prev_or_next_block_callback($block, $parent_block, $prev_or_next)
    {
        if (! isset($prev_or_next)) {
            return '';
        }

        return get_comment_delimited_block_content(
            'tests/inserted-block',
            [
                'prev_or_next' => $prev_or_next['blockName'],
            ],
            '',
        );
    }

    /**
     * @ticket 59327
     * @ticket 59412
     *
     * @covers ::traverse_and_serialize_blocks
     *
     * @dataProvider data_serialize_identity_from_parsed
     *
     * @param string $original Original block markup.
     */
    public function test_traverse_and_serialize_identity_from_parsed($original)
    {
        $blocks = parse_blocks($original);

        $actual = traverse_and_serialize_blocks($blocks);

        $this->assertSame($original, $actual);
    }

    /**
     * @ticket 59313
     *
     * @covers ::traverse_and_serialize_blocks
     */
    public function test_traverse_and_serialize_blocks_do_not_insert_in_void_block()
    {
        $markup = '<!-- wp:void /-->';
        $blocks = parse_blocks($markup);

        $actual = traverse_and_serialize_blocks(
            $blocks,
            [ __CLASS__, 'insert_next_to_child_blocks_callback' ],
            [ __CLASS__, 'insert_next_to_child_blocks_callback' ],
        );

        $this->assertSame($markup, $actual);
    }

    /**
     * @ticket 59313
     *
     * @covers ::traverse_and_serialize_blocks
     */
    public function test_traverse_and_serialize_blocks_do_not_insert_in_empty_parent_block()
    {
        $markup = '<!-- wp:outer --><div class="wp-block-outer"></div><!-- /wp:outer -->';
        $blocks = parse_blocks($markup);

        $actual = traverse_and_serialize_blocks(
            $blocks,
            [ __CLASS__, 'insert_next_to_child_blocks_callback' ],
            [ __CLASS__, 'insert_next_to_child_blocks_callback' ],
        );

        $this->assertSame($markup, $actual);
    }
}
