<?php /** @var \nochso\WriteMe\Placeholder\API\Template $this */ ?>
This is a short summary of namespaces, classes, interfaces and traits.

<?php foreach ($this->getNamespaces() as $namespace): ?>
- `N` `<?= $namespace ?>`
<?php foreach ($this->getClassesInNamespace($namespace) as $class): ?>
<?= $this->indent(1) ?>- `<?= $this->getShortClassType($class) ?>` <?= $this->mergeClassNameWithShortDescription($class, '`%s`') ?>

<?php endforeach; ?>
<?php endforeach; ?>
