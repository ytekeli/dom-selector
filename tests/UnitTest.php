<?php

declare(strict_types=1);

namespace Tests;

use DOMSelector\DOMSelector;
use DOMSelector\Formatters\Decimal;
use DOMSelector\Formatters\Integer;
use DOMSelector\Providers\TypeProvider;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class UnitTest extends TestCase
{
    public function testYamlLoaders()
    {
        $selector1 = DOMSelector::fromYamlString(file_get_contents('tests/data/files/basic.yaml'));
        $selector2 = DOMSelector::fromYamlFile('tests/data/files/basic.yaml');

        $this->assertInstanceOf(DOMSelector::class, $selector1);
        $this->assertInstanceOf(DOMSelector::class, $selector2);
        $this->assertSame($selector1->getConfig(), $selector2->getConfig());
    }

    /**
     * @throws Exception
     */
    public function testExtractFromFile()
    {
        $selector = new DOMSelector([
            'h1' => [
                'css'  => 'div.jumbotron h1',
                'type' => 'Text',
            ],
        ]);

        $this->assertSame('My First Bootstrap Page', $selector->extractFromFile('tests/data/files/basic.html')['h1']);
    }

    /**
     * @throws Exception
     */
    public function testExtractFromFileException()
    {
        $selector = new DOMSelector([]);

        $this->expectException(Exception::class);

        $selector->extractFromFile('this-file-doesnt-exists.html');
    }

    /**
     * @throws Exception
     * @throws ClientExceptionInterface
     */
    public function testExtractFromUrl()
    {
        $streamMock = Mockery::mock(StreamInterface::class);
        /** @phpstan-ignore-next-line */
        $streamMock
            ->shouldReceive('getContents')
            ->once()
            ->andReturn(\file_get_contents('tests/data/files/basic.html'));
        $responseMock = Mockery::mock(ResponseInterface::class);
        /** @phpstan-ignore-next-line */
        $responseMock
            ->shouldReceive('getBody')
            ->once()
            ->andReturn($streamMock);
        $clientMock = Mockery::mock(ClientInterface::class);
        /** @phpstan-ignore-next-line */
        $clientMock
            ->shouldReceive('sendRequest')
            ->once()
            ->andReturn($responseMock);

        $selector = new DOMSelector([
            'h1' => [
                'css'  => 'div.jumbotron h1',
                'type' => 'Text',
            ],
        ]);
        $extracted = $selector->extractFromUrl('https://example.com/', $clientMock);
        $this->assertSame('My First Bootstrap Page', $extracted['h1']);
    }

    public function testExtractFromUrlException()
    {
        $mock = new MockHandler([
            new RequestException('Error Communicating with Server', new Request('GET', 'test')),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->expectException(Exception::class);

        $selector = new DOMSelector([]);
        $selector->extractFromUrl('https://example.com/', $client);
    }

    public function testTypeAttribute()
    {
        $yaml_string = '
        img:
            css: "img"
            type: Attribute
            attribute: width';

        $selector = DOMSelector::fromYamlString($yaml_string)
            ->extract('<img src="photo.jpg" width="80" height="80" />');

        $this->assertEquals(80, $selector['img']);
    }

    public function testAttributeWithSingleFormatter()
    {
        $yaml_string = '
        width:
            css: "img"
            type: Attribute
            attribute: width
            format: 
                - Integer';

        $selector = DOMSelector::fromYamlString($yaml_string, [new Integer()])
            ->extract('<img src="photo.jpg" width="200" height="200" />');

        $this->assertSame('integer', gettype($selector['width']));
        $this->assertSame(1000, $selector['width'] * 5);
    }

    public function testAttributeWithMultipleFormat()
    {
        $yaml_string = '
        width:
            css: "img"
            type: Attribute
            attribute: width
            format: 
                - Integer
                - Decimal
        height:
            css: "img"
            type: Attribute
            attribute: height
            format: 
                - Decimal
                - Integer';

        $selector = DOMSelector::fromYamlString($yaml_string, [new Integer(), new Decimal()])
            ->extract('<img src="photo.jpg" width="200" height="200" />');

        $this->assertSame('double', gettype($selector['width']));
        $this->assertSame(200.00, $selector['width']);
        $this->assertSame('integer', gettype($selector['height']));
        $this->assertSame(200, $selector['height']);
        $this->assertEquals($selector['width'], $selector['height']);
        $this->assertNotSame($selector['width'], $selector['height']);
    }

    public function testTypeHtml()
    {
        $yaml_string = '
        content:
            css: "ul li"
            type: Html
        text:
            css: "ul"
            type: Text';

        $selector = DOMSelector::fromYamlString($yaml_string)
            ->extract('<ul><li><strong>STRONG!</strong></li></ul>');

        $this->assertEquals('<strong>STRONG!</strong>', $selector['content']);
        $this->assertEquals('STRONG!', $selector['text']);
    }

    public function testTypeImage()
    {
        $yaml_string = '
        img:
            css: "img"
            type: Image';

        $selector = DOMSelector::fromYamlString($yaml_string)
            ->extract('<img src="photo.jpg" width="80" height="80" />');

        $this->assertEquals('photo.jpg', $selector['img']);
    }

    public function testTypeLink()
    {
        $yaml_string = '
        link:
            css: "a"
            type: Link
        text:
            css: "a"
            type: Text';

        $selector = DOMSelector::fromYamlString($yaml_string)
            ->extract('<div><a href="https://example.com/">Click Here!</a> </div>');

        $this->assertEquals('https://example.com/', $selector['link']);
        $this->assertEquals('Click Here!', $selector['text']);
    }

    public function testTypeText()
    {
        $yaml_string = '
        content:
            css: "h1"
            type: Text';

        $selector = DOMSelector::fromYamlString($yaml_string)->extract('<div><h1>Hey Bro :)</h1></div>');

        $this->assertEquals('Hey Bro :)', $selector['content']);
    }

    public function testMissingCss()
    {
        $yaml_string = '
        content:
            type: Text';

        $selector = DOMSelector::fromYamlString($yaml_string)->extract('<div></div>');

        $this->assertEquals([], $selector['content']);
    }

    public function testDefaultItemType()
    {
        $yaml_string = '
        content:
            css: "div"';

        $selector = DOMSelector::fromYamlString($yaml_string)
            ->extract('<div><h1><a href="https://example.com/">Click Here!</a></h1></div>');

        $this->assertEquals('Click Here!', $selector['content']);
    }

    public function testGetChildItem()
    {
        $yaml_string = '
        items:
            css: "div.items"
            type: Text
            children:
                name:
                    css: "p"
                    type: Text
                value:
                    css: "span"
                    type: Text';

        $selector = DOMSelector::fromYamlString($yaml_string)
            ->extract('<div class="items"><p>key</p><span>value</span></div>');

        $this->assertEquals([
            'items' => [
                'name'  => 'key',
                'value' => 'value',
            ],
        ], $selector);
    }

    public function testMultiple()
    {
        $yaml_string = '
        items:
            css: "ul.items li"
            multiple: True';

        $selector = DOMSelector::fromYamlString($yaml_string)
            ->extract('<ul class="items"><li>One</li><li>Two</li><li>Three</li></ul>');

        $this->assertEquals([
            'items' => [
                'One', 'Two', 'Three',
            ],
        ], $selector);
    }

    public function testMultipleWithChildren()
    {
        $yaml_string = '
        items:
            css: "ul li"
            multiple: True
            children:
                firstname:
                    css: ".key"
                    type: Text
                lastname:
                    css: ".value"
                    type: Text';

        $html = '
        <ul>
            <li><p class="key">John</p><p class="value">Doe</p></li>
            <li><p class="key">Jane</p><p class="value">Doe</p></li>
        </ul>';

        $selector = DOMSelector::fromYamlString($yaml_string)->extract($html);

        $this->assertEquals([
            'items' => [
                0 => [
                    'firstname' => 'John',
                    'lastname'  => 'Doe',
                ],
                1 => [
                    'firstname' => 'Jane',
                    'lastname'  => 'Doe',
                ],
            ],
        ], $selector);
    }

    public function testInitializingFormatter()
    {
        $yaml_string = '
        test:
            css: "h1"
            type: Text';

        $selector = DOMSelector::fromYamlString($yaml_string, [new Integer()]);

        $this->assertEquals(['Integer' => new Integer()], $selector->getFormatters());
        $this->assertEquals('Integer', $selector->getFormatter('Integer')->getName());
    }

    public function testFormatterSingle()
    {
        $yaml_string = '
        string:
            css: "p"
            type: Text
        integer:
            css: "p"
            type: Text
            format: Integer';

        $selector = DOMSelector::fromYamlString($yaml_string, [new Integer()])->extract('<p>1</p>');

        $this->assertEquals('string', gettype($selector['string']));
        $this->assertEquals('integer', gettype($selector['integer']));
    }

    public function testFormatterMultiple()
    {
        $yaml_string = '
        decimal:
            css: "p"
            type: Text
            format: 
                - Integer
                - Decimal';

        $selector = DOMSelector::fromYamlString($yaml_string, [new Integer(), new Decimal()])->extract('<p>1</p>');

        $this->assertSame(1.00, $selector['decimal']);
        $this->assertSame('double', gettype($selector['decimal']));
    }

    public function testTypeProviderTypes()
    {
        $typeProvider = new TypeProvider();

        $this->assertTrue(count($typeProvider->types()) > 0);
    }

    public function testTypeProviderInvalidTypeException()
    {
        $this->expectExceptionObject(new Exception('TypeIsNotExists not found!'));

        $typeProvider = new TypeProvider();
        $typeProvider->add('TypeIsNotExists');
    }
}
