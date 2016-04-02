<?php
namespace nochso\WriteMe\Test;

use nochso\WriteMe\Converter;
use nochso\WriteMe\Document;
use nochso\WriteMe\Placeholder\Frontmatter;
use nochso\WriteMe\Placeholder\PlaceholderCollection;
use nochso\WriteMe\Placeholder\TOC;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Make sure that Frontmatter is replaced BEFORE the table of contents is created.
     * Otherwise the TOC would be dirty.
     */
    public function testConvert_TOC_MustBeRunLast()
    {
        $doc = new Document("---\nfoo: bar\n---\n@toc@\n# variable header @foo@");
        $converter = new Converter();
        $placeholders = new PlaceholderCollection([new TOC(), new Frontmatter()]);
        $converter->convert($doc, $placeholders);
        $this->assertSame("- [variable header bar](#variable-header-bar)\n# variable header bar", $doc->getContent());
    }

    /**
     * Make sure that Frontmatter is replaced BEFORE the table of contents is created.
     * Otherwise the TOC would be dirty.
     */
    public function testConvert_TOC_MustBeRunLastEachTime()
    {
        $doc = new Document("---\nfoo: bar\n---\n@toc@\n@toc@\n# variable header @foo@");
        $converter = new Converter();
        $placeholders = new PlaceholderCollection([new TOC(), new Frontmatter()]);
        $converter->convert($doc, $placeholders);
        $this->assertSame("- [variable header bar](#variable-header-bar)\n- [variable header bar](#variable-header-bar)\n# variable header bar", $doc->getContent());
    }

    public function testConvert_CallsMustNotBeRecognizedAsFrontmatterPlaceholders()
    {
        $doc = new Document("---\ntoc: {some: setting}\n---\n@toc@");
        $converter = new Converter();
        $placeholders = new PlaceholderCollection([new TOC(), new Frontmatter()]);
        $converter->convert($doc, $placeholders);
        $this->assertSame('', $doc->getContent());
    }
}
