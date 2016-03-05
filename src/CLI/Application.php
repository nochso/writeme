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
use nochso\WriteMe\Interfaces\Placeholder;
use nochso\WriteMe\Placeholder\API;
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
        $this->addPlaceholder(new API());
        $this->addPlaceholder(new TOC());
        $this->converter = new Converter();
    }

    public function addPlaceholder(Placeholder $placeholder)
    {
        $this->placeholders[$placeholder->getIdentifier()] = $placeholder;
    }

    public function run()
    {
        $this->stdio->outln($this->version->getInfo());
        $this->stdio->outln();
        try {
            $getopt = $this->context->getopt($this->getOptions());
            $this->validate($getopt);
            $sourceFile = $getopt->get(1);
            if ($sourceFile === null) {
                $this->showHelp();
                exit(Status::USAGE);
            }
            if (!is_file($sourceFile)) {
                throw new \RuntimeException('File not found: ' . $sourceFile);
            }
            $doc = new Document(file_get_contents($sourceFile));
            $this->converter->convert($doc, $this->placeholders);
            $this->stdio->outln($doc->getContent());
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
