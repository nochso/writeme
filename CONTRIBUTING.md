# Contributing

Contributions of any kind are welcome!

Of course your pull requests will be considered even if you don't follow these guidelines. However it will definitely speed up the process :) 

## Code style

This project follows the [PSR-2](http://www.php-fig.org/psr/psr-2/) coding style guide and [PSR-4](http://www.php-fig.org/psr/psr-4/) autoloading standard.

Try to **avoid deeply nested control structures**. Ideally each method should only have one level of indentation.
This is not a hard rule, but it generally makes it easier to read and test.

**Avoid loose arrays** of arrays of strings. It's likely that you want to **create a new class** to represent what you mean.

**PHPDoc** comments should **begin with the name of the element**:

```
/**
 * Foobar allows fooing bar.
 */
class Foobar
{
    /**
     * Reset the process of fooing bar.
     */
     public function reset()
     {
...
```

## Commit messages
Please try to use the following commit template. You can set up your local clone to use it automatically:

`git config commit.template <path to template>`

```
# <type>: (If applied, this commit will...) <subject> (Max 50 char)
# |<----  Using a Maximum Of 50 Characters  ---->|


# Explain why this change is being made
# |<----   Try To Limit Each Line to a Maximum Of 72 Characters   ---->|

# Provide links or keys to any relevant tickets, articles or other resources
# Example: Github issue #23

# --- COMMIT END ---
# Type can be
#    feat (new feature)
#    fix (bug fix)
#    docs (changes to documentation)
#    style (formatting, missing semi colons, etc; no code change)
#    refactor (refactoring production code)
#    test (adding missing tests, refactoring tests; no production code change)
#    chore (updating grunt tasks etc; no production code change)
# --------------------
# Remember to
#    Separate subject from body with a blank line
#    Limit the subject line to 50 characters
#    Capitalize the subject line
#    Do not end the subject line with a period
#    Use the imperative mood in the subject line
#    Wrap the body at 72 characters
#    Use the body to explain what and why vs. how
#    Can use multiple lines with "-" for bullet points in body
# --------------------
# For more information about this template, check out
# https://gist.github.com/adeekshith/cd4c95a064977cdc6c50
```
