<?php // @formatter:off ?>
<?php /** @var \nochso\WriteMe\Placeholder\API\Template $this */ ?>
This is an auto-generated documentation of namespaces, classes, interfaces, traits and <?= implode('/', $this->getVisibilityList()) ?> methods.

<?php foreach ($this->getNamespaces() as $namespace): ?>
<?= $this->header(1) ?> Namespace <?= $namespace ?>

@toc.sub(1)@

<?php foreach ($this->getClassesInNamespace($namespace) as $class): ?>
* * * * *
<?= $this->header(2) ?> <?= $this->getLongClassType($class) ?> <?= $class->getShortName() ?><?= $this->getClassModifierSummary($class, ', ', ' (%s)') ?>

```php
<?= $this->formatClass($class) ?>

```

<?= $this->getClassDocBlock($class)->getText() ?>


@toc.sub(1)@

<?php foreach ($this->getVisibleMethods($class) as $method): ?>
<?php $methodDoc = $this->getMethodDocBlock($method); ?>
<?= $this->header(3) ?> <?= $method->getName() ?>()

```php
<?= $this->formatMethod($method) ?>

```

<?php if ($methodDoc->getShortDescription() !== ''): ?>
<?= $this->mergeMethodNameWithShortDescription($method, '**%s**') ?>
<?php endif; ?>


<?php $returnTypes = $method->getDocBlockReturnTypes() ?>
<?php foreach ($method->getParameters() as $parameter): ?>
<?= $this->formatParameter($method, $parameter) ?>

<?php endforeach; ?>

<?php $returnTag = $this->getReturnTag($method); ?>
returns <?php if (count($returnTypes) > 0): ?>`<?= implode('|', $returnTypes) ?>`<?php else: ?>*nothing*<?php endif; ?>
<?php if ($returnTag !== null && $returnTag->getDescription() !== ''): ?>
 &mdash; <?= print_r($returnTag->getDescription(), true) ?>
<?php endif; ?>
<?php if ($method->returnsReference()): ?> as reference<?php endif; ?>


<?= $this->getMethodDocBlock($method)->getLongDescription() ?>

<?php endforeach; ?>

<?php endforeach; ?>
<?php endforeach; ?>
