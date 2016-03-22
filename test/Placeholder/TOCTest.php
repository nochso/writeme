<?php
namespace nochso\WriteMe\Test\Placeholder;

use nochso\WriteMe\Document;
use nochso\WriteMe\Placeholder\TOC;

class TOCTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider applyProvider
     *
     * @param string $input
     * @param string $expected
     */
    public function testApply($input, $expected)
    {
        $document = new Document($input);
        $toc = new TOC();
        $toc->apply($document);
        $this->assertSame($expected, $document->getContent());
    }

    public function applyProvider()
    {
        return [
            [
                '@toc@',
                '',
            ],
            [
                '',
                '',
            ],
            [
                "@toc@\n# a",
                "- [a](#a)\n# a",
            ],
            'Links must be stripped from headers' => [
                "@toc@\n# [0.3.2] - 2016-03-16",
                "- [0.3.2 - 2016-03-16](#032---2016-03-16)\n# [0.3.2] - 2016-03-16",
            ],
            'Only links must be stripped from headers' => [
                "@toc@\n# [link text](target.md) in **header**",
                "- [link text in **header**](#link-text-in-header)\n# [link text](target.md) in **header**",
            ],
        ];
    }
}
