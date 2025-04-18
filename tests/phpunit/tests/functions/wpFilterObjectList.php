<?php

/**
 * Test wp_filter_object_list().
 *
 * @group functions
 * @covers ::wp_filter_object_list
 */
class Tests_Functions_wpFilterObjectList extends WP_UnitTestCase
{
    public $object_list = [];
    public $array_list  = [];

    public function set_up()
    {
        parent::set_up();
        $this->array_list['foo'] = [
            'name'   => 'foo',
            'id'     => 'f',
            'field1' => true,
            'field2' => true,
            'field3' => true,
            'field4' => [ 'red' ],
        ];
        $this->array_list['bar'] = [
            'name'   => 'bar',
            'id'     => 'b',
            'field1' => true,
            'field2' => true,
            'field3' => false,
            'field4' => [ 'green' ],
        ];
        $this->array_list['baz'] = [
            'name'   => 'baz',
            'id'     => 'z',
            'field1' => true,
            'field2' => false,
            'field3' => false,
            'field4' => [ 'blue' ],
        ];
        foreach ($this->array_list as $key => $value) {
            $this->object_list[ $key ] = (object) $value;
        }
    }

    public function test_filter_object_list_and()
    {
        $list = wp_filter_object_list(
            $this->object_list,
            [
                'field1' => true,
                'field2' => true,
            ],
            'AND',
        );
        $this->assertCount(2, $list);
        $this->assertArrayHasKey('foo', $list);
        $this->assertArrayHasKey('bar', $list);
    }

    public function test_filter_object_list_or()
    {
        $list = wp_filter_object_list(
            $this->object_list,
            [
                'field1' => true,
                'field2' => true,
            ],
            'OR',
        );
        $this->assertCount(3, $list);
        $this->assertArrayHasKey('foo', $list);
        $this->assertArrayHasKey('bar', $list);
        $this->assertArrayHasKey('baz', $list);
    }

    public function test_filter_object_list_not()
    {
        $list = wp_filter_object_list(
            $this->object_list,
            [
                'field2' => true,
                'field3' => true,
            ],
            'NOT',
        );
        $this->assertCount(1, $list);
        $this->assertArrayHasKey('baz', $list);
    }

    public function test_filter_object_list_and_field()
    {
        $list = wp_filter_object_list(
            $this->object_list,
            [
                'field1' => true,
                'field2' => true,
            ],
            'AND',
            'name',
        );
        $this->assertSame(
            [
                'foo' => 'foo',
                'bar' => 'bar',
            ],
            $list,
        );
    }

    public function test_filter_object_list_or_field()
    {
        $list = wp_filter_object_list(
            $this->object_list,
            [
                'field2' => true,
                'field3' => true,
            ],
            'OR',
            'name',
        );
        $this->assertSame(
            [
                'foo' => 'foo',
                'bar' => 'bar',
            ],
            $list,
        );
    }

    public function test_filter_object_list_not_field()
    {
        $list = wp_filter_object_list(
            $this->object_list,
            [
                'field2' => true,
                'field3' => true,
            ],
            'NOT',
            'name',
        );
        $this->assertSame([ 'baz' => 'baz' ], $list);
    }

    public function test_filter_object_list_nested_array_and()
    {
        $list = wp_filter_object_list($this->object_list, [ 'field4' => [ 'blue' ] ], 'AND');
        $this->assertCount(1, $list);
        $this->assertArrayHasKey('baz', $list);
    }

    public function test_filter_object_list_nested_array_not()
    {
        $list = wp_filter_object_list($this->object_list, [ 'field4' => [ 'red' ] ], 'NOT');
        $this->assertCount(2, $list);
        $this->assertArrayHasKey('bar', $list);
        $this->assertArrayHasKey('baz', $list);
    }

    public function test_filter_object_list_nested_array_or()
    {
        $list = wp_filter_object_list(
            $this->object_list,
            [
                'field3' => true,
                'field4' => [ 'blue' ],
            ],
            'OR',
        );
        $this->assertCount(2, $list);
        $this->assertArrayHasKey('foo', $list);
        $this->assertArrayHasKey('baz', $list);
    }

    public function test_filter_object_list_nested_array_or_singular()
    {
        $list = wp_filter_object_list($this->object_list, [ 'field4' => [ 'blue' ] ], 'OR');
        $this->assertCount(1, $list);
        $this->assertArrayHasKey('baz', $list);
    }

    public function test_filter_object_list_nested_array_and_field()
    {
        $list = wp_filter_object_list($this->object_list, [ 'field4' => [ 'blue' ] ], 'AND', 'name');
        $this->assertSame([ 'baz' => 'baz' ], $list);
    }

    public function test_filter_object_list_nested_array_not_field()
    {
        $list = wp_filter_object_list($this->object_list, [ 'field4' => [ 'green' ] ], 'NOT', 'name');
        $this->assertSame(
            [
                'foo' => 'foo',
                'baz' => 'baz',
            ],
            $list,
        );
    }

    public function test_filter_object_list_nested_array_or_field()
    {
        $list = wp_filter_object_list(
            $this->object_list,
            [
                'field3' => true,
                'field4' => [ 'blue' ],
            ],
            'OR',
            'name',
        );
        $this->assertSame(
            [
                'foo' => 'foo',
                'baz' => 'baz',
            ],
            $list,
        );
    }
}
