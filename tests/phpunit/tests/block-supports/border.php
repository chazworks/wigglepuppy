<?php

/**
 * @group block-supports
 *
 * @covers ::wp_apply_border_support
 */
class Tests_Block_Supports_Border extends WP_UnitTestCase
{
    /**
     * @var string|null
     */
    private $test_block_name;

    public function set_up()
    {
        parent::set_up();
        $this->test_block_name = null;
    }

    public function tear_down()
    {
        unregister_block_type($this->test_block_name);
        $this->test_block_name = null;
        parent::tear_down();
    }

    /**
     * @ticket 55505
     */
    public function test_border_color_slug_with_numbers_is_kebab_cased_properly()
    {
        $this->test_block_name = 'test/border-color-slug-with-numbers-is-kebab-cased-properly';
        register_block_type(
            $this->test_block_name,
            [
                'api_version' => 2,
                'attributes'  => [
                    'borderColor' => [
                        'type' => 'string',
                    ],
                    'style'       => [
                        'type' => 'object',
                    ],
                ],
                'supports'    => [
                    '__experimentalBorder' => [
                        'color'  => true,
                        'radius' => true,
                        'width'  => true,
                        'style'  => true,
                    ],
                ],
            ],
        );
        $registry   = WP_Block_Type_Registry::get_instance();
        $block_type = $registry->get_registered($this->test_block_name);
        $block_atts = [
            'borderColor' => 'red',
            'style'       => [
                'border' => [
                    'radius' => '10px',
                    'width'  => '1px',
                    'style'  => 'dashed',
                ],
            ],
        ];

        $actual   = wp_apply_border_support($block_type, $block_atts);
        $expected = [
            'class' => 'has-border-color has-red-border-color',
            'style' => 'border-radius:10px;border-style:dashed;border-width:1px;',
        ];

        $this->assertSame($expected, $actual);
    }

    /**
     * @ticket 55505
     */
    public function test_border_with_skipped_serialization_block_supports()
    {
        $this->test_block_name = 'test/border-with-skipped-serialization-block-supports';
        register_block_type(
            $this->test_block_name,
            [
                'api_version' => 2,
                'attributes'  => [
                    'style' => [
                        'type' => 'object',
                    ],
                ],
                'supports'    => [
                    '__experimentalBorder' => [
                        'color'                           => true,
                        'radius'                          => true,
                        'width'                           => true,
                        'style'                           => true,
                        '__experimentalSkipSerialization' => true,
                    ],
                ],
            ],
        );
        $registry   = WP_Block_Type_Registry::get_instance();
        $block_type = $registry->get_registered($this->test_block_name);
        $block_atts = [
            'style' => [
                'border' => [
                    'color'  => '#eeeeee',
                    'width'  => '1px',
                    'style'  => 'dotted',
                    'radius' => '10px',
                ],
            ],
        ];

        $actual   = wp_apply_border_support($block_type, $block_atts);
        $expected = [];

        $this->assertSame($expected, $actual);
    }

    /**
     * @ticket 55505
     */
    public function test_radius_with_individual_skipped_serialization_block_supports()
    {
        $this->test_block_name = 'test/radius-with-individual-skipped-serialization-block-supports';
        register_block_type(
            $this->test_block_name,
            [
                'api_version' => 2,
                'attributes'  => [
                    'style' => [
                        'type' => 'object',
                    ],
                ],
                'supports'    => [
                    '__experimentalBorder' => [
                        'color'                           => true,
                        'radius'                          => true,
                        'width'                           => true,
                        'style'                           => true,
                        '__experimentalSkipSerialization' => [ 'radius', 'color' ],
                    ],
                ],
            ],
        );
        $registry   = WP_Block_Type_Registry::get_instance();
        $block_type = $registry->get_registered($this->test_block_name);
        $block_atts = [
            'style' => [
                'border' => [
                    'color'  => '#eeeeee',
                    'width'  => '1px',
                    'style'  => 'dotted',
                    'radius' => '10px',
                ],
            ],
        ];

        $actual   = wp_apply_border_support($block_type, $block_atts);
        $expected = [
            'style' => 'border-style:dotted;border-width:1px;',
        ];

        $this->assertSame($expected, $actual);
    }
}
