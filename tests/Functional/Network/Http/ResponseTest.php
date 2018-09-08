<?php declare(strict_types=1);

namespace Igni\Tests\Functional\Network\Http;

use Igni\Exception\RuntimeException;
use Igni\Network\Exception\InvalidArgumentException;
use Igni\Network\Http\Response;
use PHPUnit\Framework\TestCase;
use JsonSerializable;

final class ResponseTest extends TestCase
{
    private const XML_STRING = <<<XML
<?xml version="1.0"?>
<bookstore>
  <book category="cooking">
    <title lang="en">Everyday Italian</title>
    <author>Giada De Laurentiis</author>
    <year>2005</year>
    <price>30.00</price>
  </book>
</bookstore>
XML;

    public function testCanInstantiate(): void
    {
        self::assertInstanceOf(Response::class, new Response());
    }

    public function testCreateHtmlResponse(): void
    {
        $html = Response::asHtml('<html/>');

        self::assertSame(['text/html'], $html->getHeader('Content-Type'));
        self::assertSame(200, $html->getStatusCode());
        self::assertSame('<html/>', (string) $html->getBody());
    }

    /**
     * @param array|JsonSerializable $data
     * @dataProvider provideJsonData
     */
    public function testCreateJsonResponse($data): void
    {
        $json = Response::asJson($data);

        self::assertSame(['application/json'], $json->getHeader('Content-Type'));
        self::assertSame(200, $json->getStatusCode());
        self::assertSame('{"test":1}', (string) $json->getBody());
    }

    public function testCreateJsonFail(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $json = Response::asJson(new \stdClass());
    }

    public function testCreateEmpty(): void
    {
        $empty = Response::empty();
        self::assertSame(['text/plain'], $empty->getHeader('Content-Type'));
        self::assertSame(200, $empty->getStatusCode());
        self::assertSame('', (string) $empty->getBody());
    }

    public function testCreateText(): void
    {
        $text = Response::asText('test');
        self::assertSame(['text/plain'], $text->getHeader('Content-Type'));
        self::assertSame(200, $text->getStatusCode());
        self::assertSame('test', (string) $text->getBody());
    }

    /**
     * @param $xml
     * @dataProvider provideXmlData
     */
    public function testCreateXml($xml): void
    {
        $text = Response::asXml($xml);
        self::assertSame(['text/xml'], $text->getHeader('Content-Type'));
        self::assertSame(200, $text->getStatusCode());
        self::assertSame(self::XML_STRING, trim((string) $text->getBody()));
    }

    public function testCreateXmlFailure(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $text = Response::asXml(new \stdClass());
    }

    public function testWithStatus(): void
    {
        $response = Response::empty();
        $newResponse = $response->withStatus(404);
        self::assertNotSame($response, $newResponse);
        self::assertSame(200, $response->getStatusCode());
        self::assertSame(404, $newResponse->getStatusCode());
    }

    public function testWriteToResponseBody(): void
    {
        $response = Response::empty();
        self::assertSame('', (string) $response->getBody());

        $response->write('test');
        self::assertSame('test', (string) $response->getBody());

        $response->write('a');
        self::assertSame('testa', (string) $response->getBody());
        $response->end();
        self::assertTrue(self::readAttribute($response, 'complete'));

        $this->expectException(RuntimeException::class);
        $response->write('1');
    }

    public function provideXmlData(): array
    {
        $xmlString = self::XML_STRING;
        $simpleXml = simplexml_load_string($xmlString);
        $domDocument = new \DOMDocument();
        $domDocument->loadXML($xmlString);
        return [
            [$simpleXml],
            [$domDocument],
            [$xmlString]
        ];
    }

    public function provideJsonData(): array
    {
        return [
            [['test' => 1]],
            [new class implements JsonSerializable {
                public function jsonSerialize(): array
                {
                    return ['test' => 1];
                }
            }]
        ];
    }
}
