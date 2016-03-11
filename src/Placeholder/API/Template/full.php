<?php /** @var \nochso\WriteMe\Placeholder\API\Template $this */ ?>
This is an auto-generated documentation of namespaces, classes, interfaces, traits and <?= implode('/', $this->getVisibilityList()) ?> methods.

<?php foreach ($this->getNamespaces() as $namespace): ?>
<?= $this->header(1) ?>Namespace <?= $namespace ?>

<?php foreach ($this->getClassesInNamespace($namespace) as $class): ?>
<?= $this->header(2) ?><?= $this->getLongClassType($class) ?> <?= $class->getShortName() ?><?= $this->getClassModifierSummary($class, ', ', ' (%s)') ?>

<?= $this->getClassDocBlock($class)->getText() ?>

<?php foreach ($this->getVisibleMethods($class) as $method): ?>
<?= $this->header(3) ?><?= $method->getShortName() ?>()

<?= $this->getMethodDocBlock($method)->getText() ?>


<?php endforeach; ?>
<?php endforeach; ?>
<?php endforeach; ?>
