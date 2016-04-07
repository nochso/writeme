# nochso/writeme

[![GitHub tag](https://img.shields.io/github/tag/nochso/writeme.svg)](https://github.com/nochso/writeme/releases)

nochso/writeme makes creating and maintaining READMEs easier by combining frontmatter and Markdown.

For example the following table of contents was generated from the `@toc@` placeholder in [WRITEME.md](WRITEME.md).

- [nochso/writeme](#nochsowriteme)
- [Introduction](#introduction)
- [Installation](#installation)
- [Requirements](#requirements)
- [Usage](#usage)
    - [Running writeme](#running-writeme)
    - [Initializing a new template](#initializing-a-new-template)
    - [Escaping placeholders](#escaping-placeholders)
    - [Specifying a target file name](#specifying-a-target-file-name)
- [Available placeholders](#available-placeholders)
    - [Frontmatter `@*@`](#frontmatter-)
    - [TOC `@toc@`](#toc-toc)
    - [API `@api@`](#api-api)
    - [Changelog `@changelog@`](#changelog-changelog)
- [License](#license)

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
The frontmatter placeholder `@answer@` will be converted to `42` by running `writeme <file>`. This is pretty basic,
however there are other [types of placeholders](#available-placeholders) you can use.

You could even write your own by implementing the `Placeholder` interface. For example the documentation of each
placeholder is automatically generated from the PHPDocs of the placeholder classes. That way this README is easily
updated.
# Installation
Installation through [Composer](https://getcomposer.org/) is preferred:

    composer require nochso/writeme

The `writeme` executable PHP file is now available in the `vendor/bin` directory.

# Requirements
This project is written for and tested with PHP 5.6, 7.0 and HHVM.

# Usage

## Running writeme

If you've required `nochso/writeme` in your project using Composer, you can run the `writeme` executable PHP file located in
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
To avoid replacing a placeholder, escape the `@` characters with backslashes: `\@example.escape\@`.

## Specifying a target file name

By default files named `WRITEME*` will be saved to `README*`. Names that are all upper/lower-case are preserved.
This default behaviour can be overriden using the CLI option `--target <filename>` or frontmatter key `target`:

```yaml
target: DOCS.md
```

# Available placeholders

## Frontmatter `@*@`

Frontmatter placeholders return values defined in the frontmatter.

You can define any kind of structure as long as it doesn't collide with the name of any other available placeholder:

```yaml
---
greet: Hello
user:
    name: [Annyong, Tobias]
key.has.dots: yes
---
@greet@ @user.name.0@!
key has dots: @key\.has\.dots@
```

Frontmatter values are accessed using dot-notation, resulting in this output:

```markdown
Hello Annyong!
key has dots: yes
```

Using dots in the keys themselves is possible by escaping them with backslashes. See the `Dot` class provided by
[nochso/omni](https://github.com/nochso/omni).

### Default options
This placeholder has no default options.


## TOC `@toc@`

TOC placeholder creates a table of contents from Markdown headers.

There are two types of tables:

`@toc@` collects **all** Markdown headers contained in the document with a
configurable maximum depth.

`@toc.sub@` collects Markdown headers that are **below** the placeholder and on the same or deeper level.
If there's a header above the placeholder, its depth will be used as a minimum depth.
If there's no header above the placeholder, the first header after the placeholder will be used for the minimum depth.
There is currently no maximum depth for `@toc.sub@`.

e.g.
```markdown
# ignore me

@toc.sub@
## sub 1
# ignore me again
```
is converted into

```markdown
# ignore me
- [sub 1](#sub-1)
## sub 1
# ignore me again
```

### Default options
```yaml
toc:
    max-depth: 3
```

* `toc.max-depth`
    * Maximum depth of header level to extract.

## API `@api@`

API creates documentation from your PHP code.

By default it will search for all `*.php` files in your project excluding the Composer `vendor` and `test*` folders.

Currently there are two placeholders, each with a different template:

- `@api.summary@`
    - Indented list of namespaces, classes and methods including the first line of PHPDocs.
- `@api.full@`
    - Verbose documentation for each class and methods.

### Default options
```yaml
api:
    file: ['*.php']
    from: [.]
    folder-exclude: [vendor, test, tests]
```

* `api.file`
    * List of file patterns to parse.
* `api.from`
    * List of folders to search files in.
* `api.folder-exclude`
    * List of folders to exclude from the search.

## Changelog `@changelog@`

Changelog fetches the most recent release notes from a CHANGELOG written in Markdown.

This placeholder is intended for changelogs following the [keep-a-changelog](http://keepachangelog.com/) conventions.
However it should work for any Markdown formatted list of releases: each release is identified by a Markdown header.
What kind of header marks a release can be specified by the `changelog.release-level` option.

### Default options
```yaml
changelog:
    max-changes: 2
    release-level: 2
    file: CHANGELOG.md
    search-depth: 2
```

* `changelog.max-changes`
    * Maximum amount of releases to include.
* `changelog.release-level`
    * The header level that represents a release header.
* `changelog.file`
    * Filename of the CHANGELOG to extract releases from.
* `changelog.search-depth`
    * How deep the folders should be searched.


# License
nochso/writeme is released under the MIT license. See the [LICENSE](LICENSE.md) for the full license text.
