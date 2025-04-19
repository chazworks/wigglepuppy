The short version:

1. Create a clean MySQL database and user. DO NOT USE AN EXISTING DATABASE or you will lose data, guaranteed.

2. $ svn up

3. Run the tests from the "trunk" directory:
   To execute a particular test:
      $ ./bin/test-php.sh tests/phpunit/tests/test_case.php
   To execute all tests:
      $ ./bin/test-php.sh

Note: The test-php.sh script will automatically create wp-tests-config.php if it doesn't exist, using 'wptest' as the default database name (or the value of LOCAL_TEST_DB_NAME environment variable if set).

Notes:

Test cases live in the 'tests' subdirectory. All files in that directory will be included by default. Extend the WP_UnitTestCase class to ensure your test is run.

phpunit will initialize and install a (more or less) complete running copy of WordPress each time it is run. This makes it possible to run functional interface and module tests against a fully working database and codebase, as opposed to pure unit tests with mock objects and stubs. Pure unit tests may be used also, of course.

Changes to the test database will be rolled back as tests are finished, to ensure a clean start next time the tests are run.

phpunit is intended to run at the command line, not via a web server.
