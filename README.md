# nochso/writeme

[![GitHub tag](https://img.shields.io/github/tag/nochso/writeme.svg)](https://github.com/nochso/writeme/releases)

nochso/writeme makes creating and maintaining READMEs easier by combining frontmatter and Markdown.

For example the following table of contents was generated from the `@toc@` placeholder in [WRITEME.md](WRITEME.md).

- [nochso/writeme](#package)
    - [Requirements](#requirements)
    - [How it works](#how-it-works)
    - [Usage](#usage)
        - [Custom frontmatter](#custom-frontmatter)
        - [Escaping placeholders](#escaping-placeholders)
        - [Specifying a target file name](#specifying-a-target-file-name)
        - [Available placeholders](#available-placeholders)
    - [License](#license)


## Requirements
PHP 5.6.0, 7.0 or higher.

## How it works
Create a file `WRITEME.md` containing YAML frontmatter and Markdown content:

```markdown
---
package: vendor/name
---
# @package@

@toc@

## Requirements
...
```

Now run `php bin/writeme WRITEME.md` and a `README.me` file will be created:

```markdown
# vendor/name

- [vendor/name](#vendor-name)
    - [Requirements](#requirements)

## Requirements
...
```

Because you've defined `package` in the frontmatter, `@package@` turns into `vendor/name`. You can freely define any
placeholders you might need.

The only exceptions are registered placeholders. For example `@toc@` was replaced with a table of contents extracted
from the Markdown headers in your content.

## Usage

### Custom frontmatter
As long as a registered placeholder does not collide with the keys defined in the frontmatter, you can define any kind
of structure:
```yaml
greet: Hello
user:
    name: [Annyong, Tobias]
```
You can access leaf nodes using dot notation (including escaping of dots, see `Dot` provided by [nochso/omni](https://github.com/nochso/omni)):

`@greet@ @user.name.0@!` turns into `Hello Annyong!`

### Escaping placeholders
To avoid replacing a placeholder, surround it with extra `@` characters: `@@ignored@@`.

Placeholders within fenced code blocks are currently ignored.

### Specifying a target file name

By default files named `WRITEME*` will be saved to `README*`. Names that are all upper/lower-case are preserved.
This default behaviour can be overriden using the CLI option `--target <filename>` or frontmatter key `target`:

```yaml
target: DOCS.md
```

### Available placeholders

#### API `@api@`

API creates documentation from your PHP code.

By default it will search for all `*.php` files in your project excluding the Composer `vendor` and `test*` folders.

Currently there are two placeholders, each with a different template:

- `@api.summary@`
    - Indented list of namespaces, classes and methods including the first line of PHPDocs.
- `@api.full@`
    - Verbose documentation for each class and methods.

##### Default options
```yaml
api:
    file: ['*.php']
    from: [.]
    folder-exclude: [vendor, test, tests]
```


#### Changelog `@changelog@`

Changelog fetches the most recent release notes from a CHANGELOG written in Markdown.

This placeholder is intended for changelogs following the [keep-a-changelog](http://keepachangelog.com/) conventions.
However it should work for any Markdown formatted list of releases: each release is identified by a Markdown header.
What kind of header marks a release can be specified by the `changelog.release-level` option.

##### Default options
```yaml
changelog:
    max-changes: 2
    release-level: 2
    file: CHANGELOG.md
    search-depth: 2
```


#### PlaceholderDocs `@placeholder-docs@`

PlaceholderDocs creates documentation for registered placeholders.

This includes the PHPDoc for the classes and their supported options.

##### Default options
```yaml
placeholder-docs:
    header-depth: 3
```


#### TOC `@toc@`

TOC placeholder creates a table of contents from Markdown headers.

##### Default options
```yaml
toc:
    max-depth: 3
```



## License
nochso/writeme is released under the MIT license. See the [LICENSE](LICENSE.md) for the full license text.
