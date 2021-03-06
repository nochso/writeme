<?php
namespace nochso\WriteMe\Test\Placeholder;

use nochso\WriteMe\Document;
use nochso\WriteMe\Interfaces\Placeholder;
use nochso\WriteMe\Placeholder\Call;

class CallTest extends \PHPUnit_Framework_TestCase
{
    public function extractFirstCallProvider()
    {
        return [
            ['@identifier@', 'identifier', null, []],
            ['@identifier(5)@', 'identifier', null, [5]],
            ['\@escaped\@ @identifier@', 'identifier', null, []],
            ['\@escaped\@@identifier@', 'identifier', null, []],
            ['\@escaped\@\@more.escaped\@@identifier@', 'identifier', null, []],
            ['@identifier.method@', 'identifier', 'method', []],
            ['@identifier.method("string param")@', 'identifier', 'method', ['string param']],
            ['@identifier.method("")@', 'identifier', 'method', ['']],
            ['@identifier.method("")@ @identifier("")@', 'identifier', 'method', ['']],
            ["@identifier.method('string param')@", 'identifier', 'method', ['string param']],
            ['@identifier.method(1)@', 'identifier', 'method', [1]],
            ['@identifier.method(true)@', 'identifier', 'method', [true]],
            ['@identifier.method([1])@', 'identifier', 'method', [[1]]],
            ['@identifier.method(["key" => "value"])@', 'identifier', 'method', [['key' => 'value']]],
            ['@identifier.method(["key" => ["nested key" => true, 2]])@', 'identifier', 'method', [['key' => ['nested key' => true, 2]]]],
            ['@identifier.method(1, 2, 3)@', 'identifier', 'method', [1, 2, 3]],
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
        $this->assertSame($document, $call->getDocument());
    }

    public function extractFirstCallNullProvider()
    {
        return [
            ['@invalid'],
            [''],
            ['invalid@'],
            ['some content'],
            ['\@escaped\@'],
            ['\@escaped\@\@anotherescaped\@'],
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

    public function replaceProvider()
    {
        return [
            'Basic replacement' => ['@foo@', 'NEW', 'NEW'],
            'Replace after escaped placeholder' => ['\@foo\@@foo@', 'NEW', '\@foo\@NEW'],
            'Replace first placeholder only' => ['@foo@ @second.foo@', 'NEW', 'NEW @second.foo@'],
            'Properly regex-escape replacement' => ['@foo@ @second.foo@', 'NEW[]-()/\\ $0 \0', 'NEW[]-()/\\ $0 \0 @second.foo@'],
        ];
    }

    /**
     * @dataProvider replaceProvider
     *
     * @param string $rawDocument
     * @param string $replacement
     * @param string $expectedContent
     */
    public function testReplace($rawDocument, $replacement, $expectedContent)
    {
        $document = new Document($rawDocument);
        $call = Call::extractFirstCall($document);
        $call->replace($replacement);
        $this->assertSame($expectedContent, $document->getContent());
    }

    public function testIsReplaced()
    {
        $document = new Document('@foo@');
        $call = Call::extractFirstCall($document);
        $this->assertFalse($call->isReplaced());
        $call->replace('');
        $this->assertTrue($call->isReplaced());
    }

    public function testReplace_MustObserveStartPositionOfCall()
    {
        $document = new Document('@foo@ @foo@');
        $call = Call::extractFirstCall($document, Placeholder::PRIORITY_FIRST, 5);
        $call->replace('I MUST BE SECOND');
        $this->assertSame('@foo@ I MUST BE SECOND', $document->getContent());
    }

    public function testGetStartPositionOfRawCall()
    {
        $document = new Document(' @test@');
        $call = Call::extractFirstCall($document);
        $this->assertSame(1, $call->getStartPositionOfRawCall());
    }

    public function testGetEndPositionOfRawCall()
    {
        $document = new Document('@test@ @test@');
        $call = Call::extractFirstCall($document, Placeholder::PRIORITY_FIRST);
        $this->assertSame(6, $call->getEndPositionOfRawCall());

        $secondCall = Call::extractFirstCall($document, Placeholder::PRIORITY_FIRST, $call->getEndPositionOfRawCall());
        $this->assertSame(13, $secondCall->getEndPositionOfRawCall(), 'End position must be absolute and not be influenced by an offset');
    }
}
