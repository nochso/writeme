<?php
namespace nochso\WriteMe\Test;

use nochso\WriteMe\Frontmatter;

class FrontmatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider extractProvider
     *
     * @param string $rawDocument
     * @param mixed  $expectedData
     * @param string $expectedContent
     */
    public function testExtract($rawDocument, $expectedData, $expectedContent)
    {
        $fm = new Frontmatter();
        $actualContent = $fm->extract($rawDocument);
        $this->assertSame($expectedContent, $actualContent);
        $this->assertSame($expectedData, $fm->getData());
    }

    /**
     * 1. expected data.
     * 2. expected content.
     * 3. raw document from file.
     *
     * @return array
     */
    public function extractProvider()
    {
        return [
            'Empty frontmatter and no content' => [
                "---\n---", [], '',
            ],
            'Empty defaults' => [
                '', [], '', 'Empty document',
            ],
            'Data only' => [
                "---\nfoo: bar\n---", ['foo' => 'bar'], '',
            ],
            'Data and content, do not add extra newlines' => [
                "---\nfoo: bar\n---\nxxx", ['foo' => 'bar'], 'xxx',
            ],
            'Data and content, keep newlines' => [
                "---\nfoo: bar\n---\nxxx\n\n", ['foo' => 'bar'], "xxx\n\n",
            ],
            'Data and no content' => [
                "---\nfoo: bar\n---\n", ['foo' => 'bar'], '',
            ],
            'Data and keep extra newlines' => [
                "---\nfoo: bar\n---\n\n\n", ['foo' => 'bar'], "\n\n",
            ],
            'Assume only frontmatter with single separator' => [
                "---\nfoo: bar\n", ['foo' => 'bar'], '',
            ],
            ["---\nfoo: bar", ['foo' => 'bar'], ''],
            ['content only', [], 'content only'],
            'Frontmatter must have newlines' => ['---xxx---', [], '---xxx---'],
            'Frontmatter must have surround newlines' => ["---xxx\n---\n", [], "---xxx\n---\n"],
            'Single YAML string should end up in an array' => ["---\nxxx\n---", ['xxx'], ''],
        ];
    }

    public function testGet()
    {
        $fm = new Frontmatter(['foo' => ['first', 'second']]);
        $this->assertNull($fm->get('missing.defaults.to.null'));
        $this->assertSame('first', $fm->get('foo.0'));
    }

    public function testSet()
    {
        $fm = new Frontmatter();
        $fm->set('foo.bar', 'baz');
        $this->assertSame('baz', $fm->get('foo.bar'));
        $fm->set('foo', 'replaced');
        $this->assertSame('replaced', $fm->get('foo'));
        $this->assertNull($fm->get('foo.bar'));
    }

    public function testToString()
    {
        $fm = new Frontmatter(['foo' => 'bar']);
        $this->assertSame("foo: bar\n", (string) $fm);
    }
}
