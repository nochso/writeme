<?php
namespace nochso\WriteMe\Test\Markdown;

use nochso\WriteMe\Document;
use nochso\WriteMe\Markdown\HeaderParser;

class HeaderParserTest extends \PHPUnit_Framework_TestCase
{
    public function extractHeadersProvider()
    {
        return [
            [
                <<<TAG
# 1-1
# 1-2
foo
## 2-1
TAG
,
                ['1-1', '1-2', '2-1'],
                [1, 1, 2],
                [0, 1, 3],
                [0, 0, 0],
            ],
            [
                <<<TAG
# duplicate
## duplicate
TAG
,
                ['duplicate', 'duplicate'],
                [1, 2],
                [0, 1],
                [0, 1],
            ],
        ];
    }

    /**
     * @param string $input
     * @param array  $expectedText
     * @param array  $expectedLevel
     * @param array  $expectedLineIndex
     * @param array  $expectedUniqueCounter
     *
     * @dataProvider extractHeadersProvider
     */
    public function testExtractHeaders(
        $input,
        array $expectedText = null,
        array $expectedLevel = null,
        array $expectedLineIndex = null,
        array $expectedUniqueCounter = null)
    {
        $parser = new HeaderParser();
        $document = new Document($input);
        $headerList = $parser->extractHeaders($document);
        $headers = $headerList->getHeaders();
        $expectedCount = count($expectedText);
        $this->assertCount($expectedCount, $headers);
        for ($i = 0; $i < $expectedCount; $i++) {
            if ($expectedText !== null) {
                $this->assertSame($expectedText[$i], $headers[$i]->getText());
            }
            if ($expectedLevel !== null) {
                $this->assertSame($expectedLevel[$i], $headers[$i]->getLevel());
            }
            if ($expectedLineIndex !== null) {
                $this->assertSame($expectedLineIndex[$i], $headers[$i]->getLineIndex());
            }
            if ($expectedUniqueCounter !== null) {
                $this->assertSame($expectedUniqueCounter[$i], $headers[$i]->getUniqueCounter());
            }
        }
    }
}
