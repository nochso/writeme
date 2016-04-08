<?php
namespace nochso\WriteMe\Test\Placeholder;

use nochso\WriteMe\Document;
use nochso\WriteMe\Placeholder\Call;
use nochso\WriteMe\Placeholder\Frontmatter;

class FrontmatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider wildcardProvider
     *
     * @param string $expected
     * @param string $input
     */
    public function testWildcard($expected, $input)
    {
        $document = new Document($input);
        $frontmatter = new Frontmatter();
        $call = Call::extractFirstCall($document);
        $frontmatter->wildcard($call);
        $this->assertSame($expected, $document->getContent());
    }

    public function wildcardProvider()
    {
        return [
            ['bar', "---\nfoo: bar\n---\n@foo@"],
            ['nested frontmatter value', "---\nfoo: {bar: nested frontmatter value}\n---\n@foo.bar@"],
        ];
    }

    public function testWildcard_WhenFrontmatterIsUnknown_MustNotBeReplaced()
    {
        $document = new Document('@foo@');
        $frontmatter = new Frontmatter();
        $call = Call::extractFirstCall($document);
        $frontmatter->wildcard($call);
        $this->assertFalse($call->isReplaced());
        $this->assertSame('@foo@', $document->getContent());
    }
}
