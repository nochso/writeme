<?php /** @var \nochso\WriteMe\Markdown\InteractiveTemplate $this */ ?>
<?php if ($this->ask('package.name', 'Enter the library or package name') !== null): ?>
# @package.name@
<?php endif; ?>
