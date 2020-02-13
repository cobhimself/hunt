# Hunt 1.5.0

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

> NOTE: The `-r` shorthand can be used as well.

### Result Context

If you would like to see a set amount of lines before and after your results, use the `--context` option. The value
provided should be the number of lines you'd like to see before and after each result.

For example, we are choosing to see 3 lines before and after the results in the given file:

`./hunt --context 3 <searchTerm> <file.txt>`

> NOTE: This option is ignored if using the `--list` option as there is no need to display context when all you want to
> see is a list of matching files.

### Trim leading spaces in results
`./hunt --trim-matches @deprecated src includes app`

When used alongside the `--context` option, as many leading spaces as possible will be removed from the results.
Indentation is preserved.

> NOTE: Only space characters are removed from the results. Leading tabs are not removed.

### Regular expression searching

The `--regex` forces the hunt to happen utilizing a regular expression term. The given string must start and end with
`/`. The regular expression is passed on to the `preg_*` PHP method. See its documentation for help.

`./hunt --regex '/PHPUnit_Framework_MockObject_MockObject/' <dir>`

The above command would match any line with "PHPUnit_Framework_MockObject_MockObject". Simple searches like this should
usually not utilize the `--regex` flag because it's more expensive. The following would match any PHPUnit call which is
not namespaced:

`./hunt --regex '/PHPUnit_.*/' <dir>`

This would cause the match to be highlighted completely. However, you can leverage regex groups to highlight only a
specific portion of the match:

`./hunt --regex '/PHPUnit_(.*)_MockObject_MockObject/' <dir>`

Now, only the content grouped by the `(.*)` group will be highlighted.

NOTE: Not having `/` before and after your regex will result in an error.

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

### Force directories to match

If you want to the returned set of results to match a specific folder path, use the `--match-path` option:

`./hunt -r --match-path 'src/' <term> <dir> [<dir>]`

Multiple file names can be provided:

`./hunt -r --match-path 'src/' --match-path 'test/' <term> <dir> [<dir>]`

Using the short option might be easier:

`./hunt -r -m 'src/' -m 'test/' <term> <dir> [<dir>]`

Regular expressions can be used as well:

`./hunt -r -m '/^src\/.*/' <term> <dir> [<dir>]`

### Force file names to match

If you want the returned set of results files to match a specific file name, use the `--match-name` option:

`./hunt -r --match-name '*Test.php' <term> <dir> [<dir>]`

Multiple file names can be provided:

`./hunt -r --match-name '*AbstractTest.php' --match-name '*TestInterface.php' <term> <dir> [<dir>]`

Using the short option might be easier:

`./hunt -r -M '*AbstractTest.php' -M '*TestInterface.php' <term> <dir> [<dir>]`

Regular expressions can be used as well:

`./hunt -r -M '/.*AbstractTest.*/' <term> <dir> [<dir>]`

### Specify a template

Out of the box, `hunt` comes with three template types: `console`, `confluence-wiki`, and `file-list`. The `console`
template is useful for seeing preliminary results of your search. The `confluence-wiki` template can be copied and
pasted into the markup macro within Confluence and it will format itself as a table with statuses for each file with
matches. The `console` template is the default template.

#### Files with matches only

If you only want to see a list of files where we've found matches, specify the `--list` option or
use `--template=file-list`. If `--list` is provided, the `--template` option is ignored.'

## Running Tests

To run the test suite for `hunt`:

```
./vendor/bin/phpunit
```

## Upcoming

 - Currently, templates are hard-coded. In the future, it will be possible to specify a twig template to use to render the
result output.
