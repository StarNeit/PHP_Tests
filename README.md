# Automated Testing

Automated tests are used in software engineering to automatically test software. Their main goal is to provide a suite of specifications and inputs to the application, enabling easier maintenance.

The goal of automated tests is not to replace human testing or to discover bugs, but to streamline development and maintenance. They enable new and experienced developers to make changes to the software without fear of breaking the unknown, because they can run the tests.

## Types of Automated Tests

### Unit Tests

The most fundamental part of automated tests are unit tests. They are meant to test code units (e.g. functions, blocks). There is no exact definition of a unit, but the smaller the better.

Unit tests should be put under `Unit/Test*.php`. Unit tests should only work with units of code, and should
*make no assumption about the infrastructure, state, HTTP, URL, etc.*.

Since there are a lot of unit tests, they need to be as fast as possible, and almost always they should not use a database connection. To circumvent the need for that, _Mocking_ is used. Mocking means replacing certain behavior with simplified versions that do not depend on external systems. For example, if a function sends email, we mock the actual sending of email, or if a function loads data from the database, we use mock factories to create the model without the database.
Use Laravel and PHP automated testing guides online to find best practices.

Sometimes code is really hard to test, because it depeds on many things. Generally in these conditions, the code needs to be refactored and made nicer.

### Integration Tests

Integration tests test a certain feature of the application. Integration tests can test a Controller for example,
but they usually test the application externally, i.e. by calling a URI of the application and testing the output.

Testing APIs, HTTP, Console commands, etc. falls under integration tests. It's desirable for integration tests to use actual databases, and not to mock.

### Browser Tests

Browse tests, although technically either unit or integration tests (depending on what they do), are separated in our suite because we need to run a separate infrastructure to support them. For frontend only applications, browser tests are done using Javascript and are broken down into unit and integration, just like PHP tests here. We don't support that yet.
Read https://laravel.com/docs/5.7/dusk for more.

**Note:** Browser tests use Laravel Dusk's chromedriver to API with Google Chrome headless, and require `google-chrome` binary to be available. To add to the confusion, chromedriver's version must match Google Chrome's version, which often does not.
When it doesn't, upgrade chromedriver to latest version first by:
```
php artisan dusk:update
```
And then if your Google Chrome version is behind (`google-chrome --version`), install the latest:
```
wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb -O google-chrome.deb
dpkg -i google-chrome.deb
```

And you should be golden!


### Acceptance Tests

Acceptance tests are end-to-end tests that test a use-case of the application. They are usually done manually but can also be automated.

## Structure
Tests are collected under _test suites_. We have three suites now, namely _Integration_, _Unit_ and _Browser_.
Under each test suite, there are test files which include test sets. It's suggested to have one file per class in the application, unless the application class is very lightweight.

Each test file should be named `Test*.php`. PHPUnit will grab all files named like that, and run them. Use `ExampleTest.php` as starting point.

Inside each file, there's a class that extends `TestCase` and supports `setUp()` and `tearDown()` functions, which will be called before/after _every single test function_. They can be used to setup environment or mock shared variables, but don't forget to call `parent::setUp()` or `parent::tearDown()` otherwise a kitten dies.

Then there will be several `function test*()` functions, each of which is one test. Under each test, we need at least 1 assertion, which makes sure something is as its expected to be (or the test fails).


### Best Practices

#### Readability
Keep tests as simple and as readable as possible, tests are not for debugging purposes, they serve as *specification* and *documentation* and help new developers onboard more rapidly, so make them as concise and self-explanatory as possible. Tests generally do not need comments, the values used in the assertions and the assertion descriptions are enough and more explanatory:

```php
$number_one = 1;
$numer_two = 2;
$this->assertNotEquals($number_one, $number_2, 'failure description goes here, e.g. "1 should not 2"');
```

#### No Branches
Tests should not include control logic. They should not include loops, conditionals (ifs, switches), or exception handling. Tests are not supposed to test edge cases, but normal cases. So just pick one of the cases and test that, instead of looping over or using conditionals.

#### Consistent
Tests *should not use random*. It might feel like using random covers the code better, but the goal of tests is not to find bugs, it's to make sure basic functionality works. Tests should be always consistent, and running them a million times should ideally yield the exact same result.

#### Simple & Fast
Tests should be simple and fast. They are rarely more than 10 lines.
Tests should not use *sleep*. To test time, mock time functions and manually move time forward, rather than waiting.

#### Fail Before Pass
Sometimes we create a test and it's not being run, but we will not know because the test suite is passing.
To prevent these headaches, when you create a test, make an assertion that will fail, run it, make sure you're sane, fix it, then run it to pass.

#### Dependencies
Tests should try hard not to be interdependent. Each test runs separately on a completely new application, and should not depend on results of another test.

## Code Coverage
One metric to determine if tests are enough is code coverage. It determines if tests have run each single line of code at least once.

That means if you have code that has two branches, you need two tests to cover it. If it has two condtionals, but they are not nested, you might be able to cover both with only two tests.
Code coverage information can be observed after running the tests.

## Environment
*WARNING:* by default automated tests wipe the database and rerun migrations. Do not run on production or when you have useful data in the database.

You can override your test environment by creating `.env.testing` in the root Laravel directory. Usually you'd just want to use a separate database for testing. So copying your `.env` file to `.env.testing` and just changing the database name is probably all you want to do there.

Tests run under the same environment as your application, so make sure that `APP_HOST` and `APP_URL` parameters are correct, or the browser tests will fail.

Browser tests use Laravel Dusk, and launch a webserver using `php artisan serve --port=80` before running, so make sure you have your webserver setup so that it's accessible from the terminal on port 80 using `APP_URL`.



## Commands

To run tests, run `./vendor/bin/phpunit`. There is a shortcut to this in the Composer scripts called `test`. So for short, you can just run `composer test`.

You can run specific tests by specifying the directory or file path. If given a directory all tests in it and its subdirectories will run.
```
composer test tests/Integration/Http/Controllers/API/AutoDescriptionsControllerTest.php
composer test tests/Unit
```

The Browser suite automatically launched the browser, and can also be separately executed with `php artisan dusk`, if you want to run your server yourself (or it's already running).

To see more information and debug tests or behavior, use
```bash
phpunit --debug
```

By default, HTML code coverage report is disabled (for performance reasons). To enable it, run:
```
./vendor/bin/phpunit --coverage-html=tests/_reports/coverage
```

The generated report is available either as HTML file under the specified path, or directly via Laravel at `/tests`.
Other configuration parameters can be seen in `phpunit.xml`.

To run browser tests alone, you can use the following:
```
phpunit --testsuite Browser --debug -v --stop-on-failure
```

It will try to access your APP_URL. If you do not have a webserver running on it, run one first:
```
php artisan serve --port=80
```
