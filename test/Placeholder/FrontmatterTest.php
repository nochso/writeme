<?php
namespace nochso\WriteMe\Test\Placeholder;

use nochso\WriteMe\Document;
use nochso\WriteMe\Placeholder\Call;
use nochso\WriteMe\Placeholder\Frontmatter;

class FrontmatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider callProvider
     *
     * @param string $expected
     * @param string $input
     */
    public function testCall($expected, $input)
    {
        $document = new Document($input);
        $frontmatter = new Frontmatter();
        $call = Call::extractFirstCall($document);
        $frontmatter->call($call);
        $this->assertSame($expected, $document->getContent());
    }

    public function callProvider()
    {
        return [
            ['bar', "---\nfoo: bar\n---\n@foo@"],
            ['nested frontmatter value', "---\nfoo: {bar: nested frontmatter value}\n---\n@foo.bar@"],
        ];
    }

    public function testCall_WhenFrontmatterIsUnknown_MustNotBeReplaced()
    {
        $document = new Document('@foo@');
        $frontmatter = new Frontmatter();
        $call = Call::extractFirstCall($document);
        $frontmatter->call($call);
        $this->assertFalse($call->isReplaced());
        $this->assertSame('@foo@', $document->getContent());
    }
}
