<?php /** @var \nochso\WriteMe\Placeholder\API\Template $this */ ?>
This is a summary of namespaces, classes, interfaces, traits and <?= implode('/', $this->getVisibilityList()) ?> methods.

<?php foreach ($this->getNamespaces() as $namespace): ?>
- `N` `<?= $namespace ?>`
<?php foreach ($this->getClassesInNamespace($namespace) as $class): ?>
<?= $this->indent(1) ?>- `<?= $this->getShortClassType($class) ?>` <?= $this->mergeClassNameWithShortDescription($class, '`%s`') ?>

<?php foreach ($this->getVisibleMethods($class) as $method): ?>
<?= $this->indent(2) ?>- <?= $this->mergeMethodNameWithShortDescription($method, '`%s`') ?>

<?php endforeach; ?>
<?php endforeach; ?>
<?php endforeach; ?>
