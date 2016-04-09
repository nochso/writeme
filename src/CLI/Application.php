<?php
namespace nochso\WriteMe\CLI;

use Aura\Cli\CliFactory;
use Aura\Cli\Context\Getopt;
use Aura\Cli\Context\OptionFactory;
use Aura\Cli\Help;
use Aura\Cli\Status;
use nochso\Diff;
use nochso\Omni\VersionInfo;
use nochso\WriteMe\Converter;
use nochso\WriteMe\Document;
use nochso\WriteMe\Markdown\InteractiveTemplate;
use nochso\WriteMe\Placeholder\API\API;
use nochso\WriteMe\Placeholder\Changelog;
use nochso\WriteMe\Placeholder\Frontmatter;
use nochso\WriteMe\Placeholder\PlaceholderCollection;
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
     * @var \nochso\WriteMe\Placeholder\PlaceholderCollection
     */
    private $placeholders;
    /**
     * @var \nochso\WriteMe\Converter
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
        $this->placeholders = new PlaceholderCollection([
            new Frontmatter(),
            new TOC(),
            new API(),
            new Changelog(),
        ]);
        $placeholderDocs = new PlaceholderDocs();
        $placeholderDocs->setPlaceholderCollection($this->placeholders);
        $this->placeholders->add($placeholderDocs);
        $this->converter = new Converter();
    }

    /**
     * @return \nochso\WriteMe\Document
     */
    public function interactiveTemplateToDocument()
    {
        $template = new InteractiveTemplate($this->stdio, $this->placeholders);
        $availableTemplates = $template->getAvailableTemplates();
        $templateIndex = $this->stdio->chooseFromList($availableTemplates, 'Choose an interactive template', true);
        $templateFilepath = $availableTemplates[$templateIndex];

        $filepath = $this->stdio->ask('Filepath of your new customized template', 'WRITEME.md');
        $targetPath = $this->stdio->ask('Filepath to final result file', 'README.md');

        $generatedContent = $template->render($templateFilepath);
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
                exit(Status::SUCCESS);
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
            $targetFile = $doc->getTargetFilepath($getopt->get('--target'));
            if ($getopt->get('--diff')) {
                $this->showDiff($doc, $targetFile);
            }
            if ($getopt->get('--dry-run')) {
                // Show full output when diff is not wanted
                if (!$getopt->get('--diff')) {
                    $this->stdio->outln('Output:');
                    $this->stdio->outln('<<green>>---<<reset>>');
                    $this->stdio->outln($doc->getContent());
                    $this->stdio->outln('<<green>>---<<reset>>');
                }
            } else {
                $doc->saveTarget($targetFile);
            }
            $this->stdio->outln(sprintf('Saved output from <<green>>%s<<reset>> to <<green>>%s<<reset>>.', $doc->getFilepath(), $targetFile));
        } catch (\Exception $e) {
            $this->stdio->exception($e);
            exit(Status::FAILURE);
        }
    }

    /**
     * @todo Make this available in templates
     */
    protected function suggestPackageName()
    {
        $explode = explode(DIRECTORY_SEPARATOR, getcwd());
        list($dir, $parentDir) = array_slice($explode, count($explode) - 2, 2);
        return $dir . DIRECTORY_SEPARATOR . $parentDir;
    }

    private function showDiff(Document $document, $existingFilepath)
    {
        $before = file_get_contents($existingFilepath);
        $after = $document->getContent();
        $diff = Diff\Diff::create($before, $after);
        $this->stdio->out('Differences to existing file:');
        if (!count($diff->getDiffLines())) {
            $this->stdio->outln(' <<yellow>>None<<reset>>');
            $this->stdio->outln();
            return;
        }
        $this->stdio->outln();
        $this->stdio->outln();
        if ($this->stdio->getStdout()->isPosix()) {
            $t = new Diff\Format\Template\POSIX();
        } else {
            $t = new Diff\Format\Template\Text();
        }
        $this->stdio->outln($t->format($diff));
        $this->stdio->outln();
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
            'diff' => 'Show differences to existing file. Can be combined with --dry-run.',
            'dry-run' => 'Display the output and do not overwrite existing file. Can be combined with --diff.',
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
