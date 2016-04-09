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
* <?= $parameter->getHints() ?> `$<?= $parameter->getName() ?>` <?= $parameter->getDescription() ?>

<?php endforeach; ?>

<?php endforeach; ?>

<?= $this->header(2, 'Default options') ?>

<?php if (count($placeholder->getDefaultOptionList()->getOptions()) === 0): ?>
This placeholder has no default options.
<?php else: ?>
```yaml
<?= $this->getOptionListYaml($placeholder->getDefaultOptionList()) ?>
```
<?php endif; ?>

<?php foreach ($placeholder->getDefaultOptionList()->getOptions() as $option): ?>
* `<?= $option->getPath() ?>`
<?= $this->indent(1, '*') ?> <?= $option->getDescription() ?>

<?php endforeach; ?>
<?php endforeach; ?>
