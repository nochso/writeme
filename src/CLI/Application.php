<?php
namespace nochso\WriteMe\CLI;

use Aura\Cli\CliFactory;
use Aura\Cli\Context\Getopt;
use Aura\Cli\Context\OptionFactory;
use Aura\Cli\Help;
use Aura\Cli\Status;
use nochso\Omni\VersionInfo;
use nochso\WriteMe\Converter;
use nochso\WriteMe\Document;
use nochso\WriteMe\Frontmatter;
use nochso\WriteMe\Interfaces\Placeholder;
use nochso\WriteMe\Markdown\InteractiveTemplate;
use nochso\WriteMe\Placeholder\API\API;
use nochso\WriteMe\Placeholder\Changelog;
use nochso\WriteMe\Placeholder\PlaceholderDocs\PlaceholderDocs;
use nochso\WriteMe\Placeholder\TOC;

/**
 * @todo Refactor into own package nochso/cli. See Stdio.
 */
final class Application
{
    /**
     * @var \nochso\WriteMe\CLI\Stdio
     */
    private $stdio;
    /**
     * @var \Aura\Cli\Context
     */
    private $context;
    /**
     * @var \nochso\WriteMe\Interfaces\Placeholder[]
     */
    private $placeholders = [];
    /**
     * @var \nochso\WriteMe\Interfaces\Converter
     */
    private $converter;

    public function __construct(array $globals = null)
    {
        $this->version = new VersionInfo('writeme', '0.1.0', '<<green>>%s<<reset>> <<yellow>>%s<<reset>>');
        $clif = new CliFactory();
        if ($globals === null) {
            $globals = $GLOBALS;
        }
        $this->context = $clif->newContext($globals);
        $this->stdio = Stdio::create();
        $placeholderDocs = new PlaceholderDocs();
        $this->addPlaceholder(new API());
        $this->addPlaceholder(new Changelog());
        $this->addPlaceholder($placeholderDocs);
        $this->addPlaceholder(new TOC());
        // Docs should know about all placeholders, but TOC must be applied last
        $placeholderDocs->setPlaceholders($this->placeholders);
        $this->converter = new Converter();
    }

    public function addPlaceholder(Placeholder $placeholder)
    {
        $this->placeholders[$placeholder->getIdentifier()] = $placeholder;
    }

    protected function suggestPackageName()
    {
        $explode = explode(DIRECTORY_SEPARATOR, getcwd());
        list($dir, $parentDir) = array_slice($explode, count($explode) - 2, 2);
        return $dir . DIRECTORY_SEPARATOR . $parentDir;
    }

    /**
     * [Optional] Interactive cli session to help user create a readme stdin. 
     */
    public function interactive()
    {
        /*
            a variable to hold key/value pars for title => content
            ex: ['license' => 'MIT']
        */
        $content = [];

        /*current working directory to use as dir*/
        $dir = getcwd();
        $suggestedPath = $this->suggestPackageName();
        $suggestedInstallCommand = 'composer require ' . str_replace(DIRECTORY_SEPARATOR, '\\', $suggestedPath);

        $getopt = $this->context->getopt($this->getOptions());

        $this->stdio->outln(sprintf(
            '<<bold black yellowbg>>Welcome to writeme interactive generateor<<reset>>'
        ));

        $this->stdio->outln(sprintf(
            'This command will walk you through creating your README.md file'
        ));

        $content['header'] = $this->stdio->ask('Package name (e.g. vendor/name)', $suggestedPath, '/^.+\/.+$/');

        $suggestedInstallCommand = 'composer require ' .  str_replace(DIRECTORY_SEPARATOR, '\\', $content['header']);

        $this->stdio->outln(sprintf(
            'Write one line description about what this package does, press enter to skip'
        ));

        $body = $this->stdio->in(1);

        if ($body) {
            $content['body'] = $body;
        }

        $this->stdio->outln(sprintf(
            "Write one-line install command [<<bold yellow>> $suggestedInstallCommand <<reset>>] :"
        ));

        $install = $this->stdio->inln(1);

        if ($install) {
            $content['install'] = $install;
        }

        $this->stdio->outln(sprintf(
            'Choose a license for this package [<<bold yellow>> MIT <<reset>>]'
        ));

        $license = $this->stdio->in(1);

        if ($license) {
            $content['license'] = $license;
        }

        $this->stdio->outln(sprintf(
            'Do you want to generate a README.md file now? [<<bold yellow>> Y/n <<reset>>]'
        ));

        $response = $this->stdio->in(1);

        if (!in_array($response, ['', 'y', 'Y'])) {
            $this->stdio->outln(sprintf(' ERROR: EXITING ... README.md file not created.  '));
            exit(Status::FAILURE);
        } else {

            /*saving the doc*/
            $template = <<<'TAG'
# @header@

@body@

## Installation

```
@install@
```

## License
This project is released under the @license@ license.
TAG;
            $doc = new Document($template, 'README.md');
            $doc->setFrontmatter(new Frontmatter($content));
            $this->converter->convert($doc, $this->placeholders);
            $generate = $doc->saveTarget($dir . '/README.md');

            if ($generate) {
                $this->stdio->outln(sprintf(' <<green bold>> YOUR README.md has been successfully created. <<reset>>'));
            }
        }
    }

    /**
     * @return \nochso\WriteMe\Document
     */
    public function interactiveTemplateToDocument()
    {
        $filepath = $this->stdio->ask('File name of the generated WRITEME file', 'WRITEME.md');
        $targetPath = $this->stdio->ask('Path to target file after conversion', 'README.md');

        $template = new InteractiveTemplate($this->stdio, $this->placeholders);
        $generatedContent = $template->render('default.php');
        $doc = new Document($generatedContent, $filepath);
        $doc->setFrontmatter($template->getFrontmatter());
        $doc->getFrontmatter()->set('target', $targetPath);
        return $doc;
    }

    public function run()
    {
        $this->stdio->outln($this->version->getInfo());
        $this->stdio->outln();
        try {
            $getopt = $this->context->getopt($this->getOptions());

            # For the interactive session. 
            if ($getopt->get('--init')) {
                $doc = $this->interactiveTemplateToDocument();
                $doc->saveRaw();
                $this->stdio->outln();
                $this->stdio->outln('Customized template written to ' . $doc->getFilepath());
                $this->converter->convert($doc, $this->placeholders);
                $targetPath = $doc->saveTarget();
                $this->stdio->outln('Converted document written to ' . $targetPath);
                exit(Status::USAGE);
            }

            $this->validate($getopt);
            $sourceFile = $getopt->get(1);
            if ($sourceFile === null) {
                $this->showHelp();
                exit(Status::USAGE);
            }

            if (!is_file($sourceFile)) {
                throw new \RuntimeException('File not found: ' . $sourceFile);
            }
            $doc = Document::fromFile($sourceFile);
            $this->converter->convert($doc, $this->placeholders);
            $targetFile = $doc->saveTarget($getopt->get('--target'));
            $this->stdio->outln(sprintf('Saved output from <<green>>%s<<reset>> to <<green>>%s<<reset>>.', $doc->getFilepath(), $targetFile));
        } catch (\Exception $e) {
            $this->stdio->exception($e);
            exit(Status::FAILURE);
        }
    }

    /**
     * @return \Aura\Cli\Help
     */
    private function createHelp()
    {
        $help = new Help(new OptionFactory());
        $help->setSummary('Write me to read me.');
        $help->setUsage(['[options] [--] <file>']);
        $help->setOptions($this->getOptions());
        $help->setDescr('Convert a WRITEME template to its README counterpart. e.g. WRITEME.md will be written to README.md. Override this using option --target or front-matter key "target"');
        return $help;
    }

    private function getOptions()
    {
        return [
            'init' => 'Initialize an Interactive session to generate a README.md file from questions',
            '#file' => 'Input file to be converted.',
            't,target:' => 'Path or name of output file. Optional if the name can be inferred otherwise (see description).',
        ];
    }

    private function showHelp()
    {
        $help = $this->createHelp();
        $this->stdio->outln($help->getHelp('writeme'));
    }

    private function validate(Getopt $getopt)
    {
        if (!$getopt->hasErrors()) {
            return;
        }
        foreach ($getopt->getErrors() as $error) {
            $this->stdio->exception($error);
        }
        $this->showHelp();
        exit(Status::USAGE);
    }
}
