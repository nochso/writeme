<?php
namespace nochso\WriteMe\Test;

use nochso\Omni\Folder;
use nochso\Omni\Path;
use nochso\WriteMe\Document;

class DocumentTest extends \PHPUnit_Framework_TestCase
{
    public function saveTargetProvider()
    {
        $tempFolder = Path::combine(sys_get_temp_dir(), 'nochso_writeme_test');
        Folder::ensure($tempFolder);
        return [
            'Specified target path' => [
                new Document(''),
                Path::combine($tempFolder, 'target.md'),
                Path::combine($tempFolder, 'target.md'),
            ],
            'WRITEME turns to README' => [
                new Document('', Path::combine($tempFolder, 'WRITEME.md')),
                null,
                Path::combine($tempFolder, 'README.md'),
            ],
            'WRITEME turns to README (case insensitive)' => [
                new Document('', Path::combine($tempFolder, 'writeme')),
                null,
                Path::combine($tempFolder, 'readme'),
            ],
            'Get target path from frontmatter' => [
                new Document("---\ntarget: " . Path::combine($tempFolder, 'target.md') . "\n---"),
                null,
                Path::combine($tempFolder, 'target.md'),
            ],
        ];
    }

    /**
     * @dataProvider saveTargetProvider
     * 
     * @param \nochso\WriteMe\Document $document
     * @param string|null              $target
     * @param string                   $expectedTarget
     */
    public function testSaveTarget(Document $document, $target, $expectedTarget)
    {
        $resultingTarget = $document->saveTarget($target);
        $this->assertSame($expectedTarget, $resultingTarget);
    }

    public function saveTargetExceptionProvider()
    {
        return [
            [new Document('')],
            [new Document('', 'can not handle this.md')],
            [new Document("---\ntarget: null\n---")],
        ];
    }

    /**
     * @dataProvider saveTargetExceptionProvider
     *
     * @param \nochso\WriteMe\Document $document
     */
    public function testSaveTarget_WhenNoTargetDetected_MustThrow(Document $document)
    {
        $this->expectException('RuntimeException');
        $document->saveTarget();
    }
}
