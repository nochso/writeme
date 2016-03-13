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

For example the following table of contents was generated from the `@@toc@@` placeholder in [WRITEME.md](WRITEME.md).

@toc@

## Requirements
PHP 5.6.0, 7.0 or higher.

## How it works
Create a file `WRITEME.md` containing YAML frontmatter and Markdown content:

```markdown
---
package: vendor/name
---
# @@package@@

@@toc@@

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

Because you've defined `package` in the frontmatter, `@@package@@` turns into `vendor/name`. You can freely define any
placeholders you might need.

The only exceptions are registered placeholders. For example `@@toc@@` was replaced with a table of contents extracted
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

`@@greet@@ @@user.name.0@@!` turns into `Hello Annyong!`

### Escaping placeholders
To avoid replacing a placeholder, surround it with extra `@` characters: `@@@ignored@@@`.

Placeholders within fenced code blocks are currently ignored.

### Specifying a target file name

By default files named `WRITEME*` will be saved to `README*`. Names that are all upper/lower-case are preserved.
This default behaviour can be overriden using the CLI option `--target <filename>` or frontmatter key `target`:

```yaml
target: DOCS.md
```

### Available placeholders
@placeholder-docs@

## License
@package@ is released under the @license.name@ license. See the [LICENSE](@license.file@) for the full license text.
