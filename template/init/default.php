<?php /** @var \nochso\WriteMe\Markdown\InteractiveTemplate $this */ ?>
<?php if ($this->ask('package.name', 'Enter the library or package name') !== null): ?>
# @package.name@
<?php endif; ?>

<?php if ($this->ask('package.description', 'Enter a description for this library or package') !== null): ?>
@package.description@
<?php endif; ?>

<?php if ($this->ask('package.install', 'Enter a composer install one-line code') !== null): ?>
# Install
```php
@package.install@
```php
<?php endif; ?>

<?php if ($this->ask('package.composer.install', 'Enter the license of the library of package') !== null): ?>
# License
## @package.composer.install@
<?php endif; ?>
