<?php
namespace nochso\WriteMe\Test\Placeholder;

use nochso\WriteMe\Document;
use nochso\WriteMe\Placeholder\Call;

class CallTest extends \PHPUnit_Framework_TestCase
{
    public function extractFirstCallProvider()
    {
        return [
            ['@identifier@', 'identifier', null, []],
            ['@@escaped@@ @identifier@', 'identifier', null, []],
            ['@@escaped@@@identifier@', 'identifier', null, []],
            ['@@escaped@@@@more.escaped@@@identifier@', 'identifier', null, []],
            ['@identifier.method@', 'identifier', 'method', []],
            ['@identifier.method "string param"@', 'identifier', 'method', ['string param']],
            ["@identifier.method 'string param'@", 'identifier', 'method', ['string param']],
            ['@identifier.method 1@', 'identifier', 'method', [1]],
            ['@identifier.method true@', 'identifier', 'method', [true]],
            ['@identifier.method [1]@', 'identifier', 'method', [[1]]],
            ['@identifier.method ["key" => "value"]@', 'identifier', 'method', [['key' => 'value']]],
            ['@identifier.method ["key" => ["nested key" => true, 2]]@', 'identifier', 'method', [['key' => ['nested key' => true, 2]]]],
            ['@identifier.method 1, 2, 3@', 'identifier', 'method', [1, 2, 3]],
            ['@identifier.firstcall@ @identifier.secondcall@', 'identifier', 'firstcall', []],
        ];
    }

    /**
     * @dataProvider extractFirstCallProvider
     * 
     * @param string      $rawDocument
     * @param string      $expectedIdentifier
     * @param string|null $expectedMethod
     * @param array       $expectedParameters
     */
    public function testExtractFirstCall($rawDocument, $expectedIdentifier, $expectedMethod, $expectedParameters)
    {
        $document = new Document($rawDocument);
        $call = Call::extractFirstCall($document);
        $this->assertSame($expectedIdentifier, $call->getIdentifier());
        $this->assertSame($expectedMethod, $call->getMethod());
        $this->assertSame($expectedParameters, $call->getParameters());
    }

    public function extractFirstCallNullProvider()
    {
        return [
            ['@invalid'],
            [''],
            ['invalid@'],
            ['some content'],
            ['@@escaped@@'],
            ['@@escaped@@@@anotherescaped@@'],
            ['john@doe.com jane@doe.com'],
        ];
    }

    /**
     * @dataProvider extractFirstCallNullProvider
     *
     * @param string $rawDocument
     */
    public function testExtractFirstCall_WhenNoneFound_MustReturnNull($rawDocument)
    {
        $document = new Document($rawDocument);
        $this->assertNull(Call::extractFirstCall($document));
    }

    public function rawCallProvider()
    {
        return [
            ['@foo@', '@foo@'],
            ['x@foo@y', '@foo@'],
            ['@foo@ @foo2@', '@foo@'],
        ];
    }

    /**
     * @dataProvider rawCallProvider
     *
     * @param string $rawDocument
     * @param string $expectedRawCall
     */
    public function testExtractFirstCall_RawCall($rawDocument, $expectedRawCall)
    {
        $document = new Document($rawDocument);
        $call = Call::extractFirstCall($document);
        $this->assertSame($expectedRawCall, $call->getRawCall());
    }
}
