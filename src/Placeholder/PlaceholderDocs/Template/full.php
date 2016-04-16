<?php /** @var \nochso\WriteMe\Placeholder\PlaceholderDocs\TemplateData $this */ ?>
<?php foreach ($this->getRelevantPlaceholders() as $placeholder): ?>
<?php $class = $this->getClassForPlaceholder($placeholder); ?>
<?php $docBlock = $this->getClassDocBlock($class); ?>

<?= $this->header(1, $class->getShortName()) ?> `@<?= $placeholder->getIdentifier() ?>@`

<?= $docBlock->getText() ?>

<?php foreach ($this->getMethodsForPlaceholder($placeholder) as $method): ?>

<?= $this->header(2) ?> `<?= $method->getDotSignature() ?>`

<?= $method->getDocBlock()->getText() ?>

<?php foreach ($method->getParametersWithoutCall() as $parameter): ?>
* `$<?= $parameter->getName() ?>
<?php if ($parameter->getReflectionParameter()->isDefaultValueAvailable()): ?>
 = <?= $parameter->getReflectionParameter()->getDefaultValueAsString() ?>
<?php endif; ?>` <?= strlen($parameter->getHints()) ? ' `' . $parameter->getHints() . '`' : '' ?>

<?php if (strlen($parameter->getDescription())): ?><?= $this->indent(1, '* ' . $parameter->getDescription()) ?><?php endif; ?>

<?php endforeach; ?>

<?php endforeach; ?>

<?php if (count($placeholder->getDefaultOptionList()->getOptions())): ?>
<?= $this->header(2, 'Default options') ?>

```yaml
<?= $this->getOptionListYaml($placeholder->getDefaultOptionList()) ?>
```
<?php endif; ?>

<?php foreach ($placeholder->getDefaultOptionList()->getOptions() as $option): ?>
* `<?= $option->getPath() ?>`
<?= $this->indent(1, '*') ?> <?= $option->getDescription() ?>

<?php endforeach; ?>
<?php endforeach; ?>
