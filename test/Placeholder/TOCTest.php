<?php
namespace nochso\WriteMe\Test\Placeholder;

use nochso\WriteMe\Document;
use nochso\WriteMe\Placeholder\Call;
use nochso\WriteMe\Placeholder\TOC;

class TOCTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider tocProvider
     *
     * @param string $input
     * @param string $expected
     */
    public function testToc($input, $expected)
    {
        $document = new Document($input);
        $toc = new TOC();
        $toc->prepare($document);
        $call = Call::extractFirstCall($document);
        $toc->toc($call);
        $this->assertSame($expected, $document->getContent());
    }

    public function tocProvider()
    {
        return [
            [
                '@toc@',
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

    /**
     * @dataProvider tocSubProvider
     *
     * @param string $input
     * @param string $expected
     */
    public function testTocSub($input, $expected)
    {
        $document = new Document($input);
        $toc = new TOC();
        $toc->prepare($document);
        $call = Call::extractFirstCall($document);
        $toc->tocSub($call);
        $this->assertSame($expected, $document->getContent());
    }

    public function tocSubProvider()
    {
        return [
            'Sub-TOC and no preceding header' => [
                <<<TAG
@toc.sub@
## 2-1
## 2-2
# ignore me
TAG
                ,
                <<<TAG
- [2-1](#2-1)
- [2-2](#2-2)
## 2-1
## 2-2
# ignore me
TAG

            ],
            'Sub-TOC' => [
                <<<TAG
# ignore me
@toc.sub@
## sub 1
# ignore me again
TAG
                ,
                <<<TAG
# ignore me
- [sub 1](#sub-1)
## sub 1
# ignore me again
TAG

            ],
        ];
    }
}
