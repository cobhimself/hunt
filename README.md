# Hunt

Hunt for text, gather its usage.

## Installation

```
git clone <hunt repo link>
composer install
```

The `hunt` command file will be symlinked into the `vendor/bin` folder if you bring `hunt` in as a
project dependency. Otherwise, the `hunt` file within the root directory will kick `hunt` off.

## Usage

### Basic

Hunt for the string "@deprecated" in any file within the `src`, `includes`, or `app` folders:

`./hunt  @deprecated src includes app`

### Recursive Searching
`./hunt --recursive @deprecated src includes app`

Note: The `-r` shorthand can be used as well.

### Trim leading spaces in results
`./hunt --trim-matches @deprecated src includes app`

### Exclude terms from matches

Sometimes your search term will return matches for your search string you'd rather not include in the results.
For example, if you wanted to return all of the instances where `PHPUnit_` is found but you do not want to include
instances of `PHPUnit_Framework_MockObject_MockObject`:

`./hunt --exclude PHPUnit_Framework_MockObject_MockObject PHPUnit_ src includes app`

### Exclude directories from matches

If you want to exclude directories from the hunt, use the --exclude-dir option.
For example, if you wanted to exclude cache folders, regardless of where they appear in the path:

`./hunt -r --exclude-dir cache <term> <dir> [<dir>] ...`

Multiple directories can be excluded by repeating the `--exclude-dir` option:

`./hunt -r --exclude-dir cache --exclude-dir vendor <term> <dir> [<dir>] ...`

However, it may be easier to use the short option name `-x`:

`./hunt -r -x cache -x vendor <term> <dir> [<dir>] ...`

Regular expressions can be provided as well:

`./hunt -r -x '/.*cache/' <term> <dir> [<dir>] ...`

Or, if you prefer, global expressions can be used:

`./hunt -r -x 'src/*/*.php <term> <dir> [<dir>] ...`

### Exclude directories from matches

If you want to exclude specific file names from the results, you can use the `--exclude-name` option:

`./hunt -r --exclude-name Test.php <term> <dir> [<dir>] ...`

Multiple file names can be provided:

`./hunt -r --exclude-name Test.php --exclude-name File.php <term> <dir> [<dir>]`

However, it may be easier to use the short option name `-X`:

`./hunt -r -X Test.php -X File.php <term> <dir> [<dir>]`

Regular expressions can be used as well:

`./hunt -r -X '/.*Test.php$/' <term> <dir> [<dir>] ...`

Alternatively, you can specify the filter using global expressions. To only return PHP files with 'Test' at the
end of their names:

`./hunt -r -X '*Test.php' <term> <dir> [<dir>] ...`

### Specify a template

Out of the box, `hunt` comes with two template types: `console` and `confluence-wiki`. The `console` template is useful
for seeing preliminary results of your search. The `confluence-wiki` template can be copied and pasted into the markup
macro within Confluence and it will format itself as a table with statuses for each file with matches.

## Running Tests

To run the test suite for `hunt`:

```
./vendor/bin/phpunit
```

## Upcoming

 - Currently, templates are hard-coded. In the future, it will be possible to specify a twig template to use to render the
result output.
 - Hunt uses "Gatherers" to gather its hunt's result. A normal string search gatherer is used by default and, while an
   option currently exists to specify the search string is a regular expression, this functionality is not
   implemented yet.
