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

@package@ makes creating and maintaining READMEs easier.

For example the table of contents was generated from the `@@toc@@` placeholder in [WRITEME.md](WRITEME.md).

@toc@

## Requirements
PHP 5.6.0, 7.0 or higher.

## Usage
Create a file `WRITEME.md` containing YAML frontmatter and Markdown content:

```markdown
---
# This is the YAML frontmatter.
package: vendor/name
toc:
    max-depth: 2 # Any headers deeper than this will not show up in the table of contents.
---
# @@package@@

@@toc@@

## Requirements
...

## Usage

### Nested
```

Now run `bin/writeme WRITEME.md` to parse the file. `@@placeholders@@` will be replaced with the contents defined in the frontmatter.

Some placeholders have a special meaning. For example `@@toc@@` will be replaced with a table of contents of the Markdown headers:

```
# vendor/name

- [vendor/name](#package)
    - [Requirements](#requirements)
    - [Usage](#usage)


## Requirements
...

## Usage

### Nested
```

To avoid replacing a placeholder, surround it with extra @ characters: `@@@ignored@@@`.

Placeholders within fenced code blocks are currently ignored.

By default files named `WRITEME*` will be saved to `README*`. This can be overriden with the `--target` CLI option or a `target` frontmatter key.

## License
@package@ is released under the @license.name@ license. See the [LICENSE](@license.file@) for the full license text.
