---
package: nochso/writeme
license:
    name: MIT
    file: LICENSE.md
toc:
    max-depth: 3
---
# @package@

[![GitHub tag](https://img.shields.io/github/tag/@package@.svg)](https://github.com/@package@/releases)

@package@ makes creating and maintaining READMEs easier by combining frontmatter and Markdown.

For example the following table of contents was generated from the `\@toc\@` placeholder in [WRITEME.md](WRITEME.md).

@toc@

# Installation
Installation through [Composer](https://getcomposer.org/) is preferred:

    composer require @package@

The `writeme` executable PHP file is now available in the `vendor/bin` directory.

# Requirements
PHP 5.6.0, 7.0 or higher.

# Introduction / example
Create a file `WRITEME.md` containing YAML frontmatter and Markdown content:

```markdown
---
package: vendor/name
---
# \@package\@

\@toc\@

# Requirements
...
```

Running `php bin/writeme WRITEME.md` will parse the template and convert it to `README.md`:

```markdown
# vendor/name

- [vendor/name](#vendor-name)
- [Requirements](#requirements)

# Requirements
...
```

Because you've defined `package` in the frontmatter, `\@package\@` turns into `vendor/name`. You can freely define any
placeholders you might need.

The only exceptions are registered placeholders. For example `\@toc\@` was replaced with a table of contents extracted
from the Markdown headers in your content.

# Usage

If you've required `@package@` in your project using Composer, you can run the `writeme` file located in `vendor/bin`:

    php vendor/bin/writeme

Run it without any arguments to get an overview of available arguments.

## Initializing a new template
writeme comes with a template for a typical Composer based project available on Packagist. You can initialize
your own WRITEME.md based on this template:

    php vendor/bin/writeme --init

Simply answer the questions. Some are optional and pressing enter will either skip them or use defaults.

Some placeholders have default settings: you will be asked if you want to override these. Your custom settings will then
be added to the YAML frontmatter.

Once you're done, you should have two new files. The template and the resulting output, usually `WRITEME.md` and `README.md`.

## Custom frontmatter
As long as a registered placeholder does not collide with the keys defined in the frontmatter, you can define any kind
of structure:
```yaml
greet: Hello
user:
    name: [Annyong, Tobias]
```
You can access leaf nodes using dot notation (including escaping of dots, see `Dot` provided by [nochso/omni](https://github.com/nochso/omni)):

`\@greet\@ \@user.name.0\@!` turns into `Hello Annyong!`

## Escaping placeholders
To avoid replacing a placeholder, escape the `@` characters with backslashes: `\\@example.escape\\@`.

## Specifying a target file name

By default files named `WRITEME*` will be saved to `README*`. Names that are all upper/lower-case are preserved.
This default behaviour can be overriden using the CLI option `--target <filename>` or frontmatter key `target`:

```yaml
target: DOCS.md
```

## Available placeholders
@placeholder-docs@

# License
@package@ is released under the @license.name@ license. See the [LICENSE](@license.file@) for the full license text.
