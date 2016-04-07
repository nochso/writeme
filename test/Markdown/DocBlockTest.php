<?php
namespace nochso\WriteMe\Test\Markdown;

use nochso\WriteMe\Markdown\DocBlock;

// To make sure existing tests of reflection-docblock still pass, the test is
// extended. Since the test class itself is used for reflection, the DocBlock
// below must mirror that docs of the parent class.

/**
 * Test class for phpDocumentor\Reflection\DocBlock
 *
 * @author
 * @copyright
 * @license
 *
 * @link
 */
class DocBlockTest extends \phpDocumentor\Reflection\DocBlockTest
{
    public function descriptionProvider()
    {
        return [
            'Must not look for tags in fenced code' => [
                'Summary',
                "```\n@notreallyatag@\n```",
                ['tag'],
                <<<TAG
/**
 * Summary
 *
 * ```
 * @notreallyatag@
 * ```
 * @tag content
 */
TAG
            ],
            'Must not look for tags in indented blocks' => [
                'Summary',
                "Example code:\n\n    @notreallyatag@",
                ['tag'],
                <<<TAG
/**
 * Summary
 *
 * Example code:
 *
 *     @notreallyatag@
 *
 * @tag content
 */
TAG
            ],
        ];
    }

    /**
     * @dataProvider descriptionProvider
     *
     * @param string   $expectedShort
     * @param string   $expectedLong
     * @param string[] $expectedTagNames
     * @param string   $comment
     */
    public function testDescription($expectedShort, $expectedLong, $expectedTagNames, $comment)
    {
        $docBlock = new DocBlock($comment);
        $this->assertSame($expectedShort, $docBlock->getShortDescription());
        $this->assertSame($expectedLong, $docBlock->getLongDescription()->getContents());
        $tags = $docBlock->getTags();
        $tagNames = array_map(function ($tag) {
            return $tag->getName();
        }, $tags);
        $this->assertSame($expectedTagNames, $tagNames);
    }
}
