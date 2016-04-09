<?php /** @var \nochso\WriteMe\Markdown\InteractiveTemplate $this */ ?>
<?php $this->ask('composer.name', 'Enter the name as used on packagist', null, '/.+/'); ?>
# @composer.name@

<?php if ($this->ask('composer.description', 'Enter a one-line description of the project (optional)') !== ''): ?>
@composer.description@
<?php endif; ?>

<?php if ($this->getStdio()->confirm('Would you like to add table of contents of all sections?')): ?>
<?php $this->askForCustomPlaceholderOptionList(\nochso\WriteMe\Placeholder\TOC::class); ?>
@toc@
<?php endif; ?>

# Installation
Installation through [Composer](https://getcomposer.org/) is preferred:

    composer require @composer.name@

<?php if ($this->getStdio()->confirm('Would you like to add a summary of all PHP classes and their methods?')): ?>
<?php $this->askForCustomPlaceholderOptionList(\nochso\WriteMe\Placeholder\API\API::class); ?>
# API summary
@api('summary')@
<?php endif; ?>

<?php if ($this->ask('license.name', 'Enter the license name e.g. MIT, BSD2, etc. (optional)') !== ''): ?>
# License
This project is released under the @license.name@ license.

<?php endif; ?>
