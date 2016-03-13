<?php /** @var \nochso\WriteMe\Placeholder\PlaceholderDocs\TemplateData $this */ ?>
<?php foreach ($this->getPlaceholders() as $placeholder): ?>
<?php $class = $this->getClassForPlaceholder($placeholder); ?>
<?php $docBlock = $this->getClassDocBlock($class); ?>

<?= $this->header(1, $class->getShortName()) ?> `@@<?= $placeholder->getIdentifier() ?>@@`

<?= $docBlock->getText() ?>


<?= $this->header(2, 'Default options') ?>

```yaml
<?= $this->getOptionListYaml($placeholder->getOptions()) ?>
```

<?php foreach ($placeholder->getOptions() as $option): ?>
* `<?= $option->getPath() ?>`
<?= $this->indent(1, '*') ?> <?= $option->getDescription() ?>

<?php endforeach; ?>
<?php endforeach; ?>
