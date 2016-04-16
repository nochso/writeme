---
package: nochso/writeme
license:
    name: MIT
    file: LICENSE.md
toc:
    max-depth: 2
api:
    header-depth: 1
---
# @package@

[![GitHub tag](https://img.shields.io/github/tag/@package@.svg)](https://github.com/@package@/releases)
@badge.writeme@

@package@ is a PHP CLI utility for maintaining README and related files.

For example the following table of contents was generated from the `\@toc\@` placeholder in [WRITEME.md](WRITEME.md).

@toc@

# Introduction
writeme can be considered a template engine with a focus on typical Markdown documents like readme, change logs,
project documentation etc. Even though it's geared towards Markdown, other Markup languages and plain text will work.

A writeme document can contain [YAML](https://learnxinyminutes.com/docs/yaml/) frontmatter and text content:

```markdown
---
answer: 42
---
@answer@
```
The frontmatter placeholder `\@answer\@` will be converted to `42` by running `writeme <file>`. This is pretty basic,
however there are other [types of placeholders](#available-placeholders) you can use.

You could even write your own by implementing the `Placeholder` interface. For example the documentation of each
placeholder is automatically generated from the PHPDocs of the placeholder classes. That way this README is easily
updated.
# Installation
Installation through [Composer](https://getcomposer.org/) is preferred:

    composer require @package@

The `writeme` executable PHP file is now available in the `vendor/bin` directory.

# Requirements
This project is written for and tested with PHP 5.6, 7.0 and HHVM.

# Usage

## Running writeme

If you've required `@package@` in your project using Composer, you can run the `writeme` executable PHP file located in
`vendor/bin`:

    vendor/bin/writeme

Run it without any arguments to get an overview of available arguments.

## Initializing a new template
writeme comes with a template for a typical Composer based project available on Packagist. You can initialize
your own WRITEME.md based on this template:

    writeme --init

Simply answer the questions. Some are optional and pressing enter will either skip them or use defaults.

Some placeholders have default settings: you will be asked if you want to override these. Your custom settings will
then be added to the YAML frontmatter.

Once you're done, you should have two new files. The template and the resulting output, usually `WRITEME.md` and
`README.md`.

## Escaping placeholders
To avoid replacing a placeholder, escape the `@` characters with backslashes: `\\@example.escape\\@`.

## Specifying a target file name

By default files named `WRITEME*` will be saved to `README*`. Names that are all upper/lower-case are preserved.
This default behaviour can be overriden using the CLI option `--target <filename>` or frontmatter key `target`:

```yaml
target: DOCS.md
```

# Available placeholders
@writeme.placeholder.docs@

# License
@package@ is released under the @license.name@ license. See the [LICENSE](@license.file@) for the full license text.
