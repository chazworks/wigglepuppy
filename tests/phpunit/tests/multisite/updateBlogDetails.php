<?php

if (is_multisite()) :

    /**
     * @group ms-site
     * @group multisite
     */
    class Tests_Multisite_UpdateBlogDetails extends WP_UnitTestCase
    {
        /**
         * If `update_blog_details()` is called with any kind of empty arguments, it
         * should return false.
         */
        public function test_update_blog_details_with_empty_args()
        {
            $result = update_blog_details(1, []);
            $this->assertFalse($result);
        }

        /**
         * If the ID passed is not that of a current site, we should expect false.
         */
        public function test_update_blog_details_invalid_blog_id()
        {
            $result = update_blog_details(999, [ 'domain' => 'example.com' ]);
            $this->assertFalse($result);
        }

        public function test_update_blog_details()
        {
            $blog_id = self::factory()->blog->create();

            $result = update_blog_details(
                $blog_id,
                [
                    'domain' => 'example.com',
                    'path'   => 'my_path/',
                ],
            );

            $this->assertTrue($result);

            $blog = get_site($blog_id);

            $this->assertSame('example.com', $blog->domain);
            $this->assertSame('/my_path/', $blog->path);
            $this->assertSame('0', $blog->spam);
        }

        /**
         * Test each of the actions that should fire in update_blog_details() depending on
         * the flag and flag value being set. Each action should fire once and should not
         * fire if a flag is already set for the given flag value.
         *
         * @param string $flag       The name of the flag being set or unset on a site.
         * @param string $flag_value '0' or '1'. The value of the flag being set.
         * @param string $action     The hook expected to fire for the flag name and flag combination.
         *
         * @dataProvider data_flag_hooks
         */
        public function test_update_blog_details_flag_action($flag, $flag_value, $hook)
        {
            $test_action_counter = new MockAction();

            $blog_id = self::factory()->blog->create();

            // Set an initial value of '1' for the flag when '0' is the flag value being tested.
            if ('0' === $flag_value) {
                update_blog_details($blog_id, [ $flag => '1' ]);
            }

            add_action($hook, [ $test_action_counter, 'action' ]);

            update_blog_details($blog_id, [ $flag => $flag_value ]);
            $blog = get_site($blog_id);

            $this->assertSame($flag_value, $blog->{$flag});

            // The hook attached to this flag should have fired once during update_blog_details().
            $this->assertSame(1, $test_action_counter->get_call_count());

            // Update the site to the exact same flag value for this flag.
            update_blog_details($blog_id, [ $flag => $flag_value ]);

            // The hook attached to this flag should not have fired again.
            $this->assertSame(1, $test_action_counter->get_call_count());
        }

        public function data_flag_hooks()
        {
            return [
                [ 'spam', '0', 'make_ham_blog' ],
                [ 'spam', '1', 'make_spam_blog' ],
                [ 'archived', '1', 'archive_blog' ],
                [ 'archived', '0', 'unarchive_blog' ],
                [ 'deleted', '1', 'make_delete_blog' ],
                [ 'deleted', '0', 'make_undelete_blog' ],
                [ 'mature', '1', 'mature_blog' ],
                [ 'mature', '0', 'unmature_blog' ],
            ];
        }

        /**
         * When the path for a site is updated with update_blog_details(), the final path
         * should have a leading and trailing slash.
         *
         * @dataProvider data_single_directory_path
         */
        public function test_update_blog_details_single_directory_path($path, $expected)
        {
            update_blog_details(1, [ 'path' => $path ]);
            $site = get_site(1);

            $this->assertSame($expected, $site->path);
        }

        public function data_single_directory_path()
        {
            return [
                [ 'my_path', '/my_path/' ],
                [ 'my_path//', '/my_path/' ],
                [ '//my_path', '/my_path/' ],
                [ 'my_path/', '/my_path/' ],
                [ '/my_path', '/my_path/' ],
                [ '/my_path/', '/my_path/' ],

                [ 'multiple/dirs', '/multiple/dirs/' ],
                [ '/multiple/dirs', '/multiple/dirs/' ],
                [ 'multiple/dirs/', '/multiple/dirs/' ],
                [ '/multiple/dirs/', '/multiple/dirs/' ],

                // update_blog_details() does not resolve multiple slashes in the middle of a path string.
                [ 'multiple///dirs', '/multiple///dirs/' ],
            ];
        }
    }
endif;
