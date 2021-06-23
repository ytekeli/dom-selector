<?php

declare(strict_types=1);

use DOMSelector\DOMSelector;
use PHPUnit\Framework\TestCase;

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

    public function testTypeAttribute()
    {
        $yaml_string = '
        img:
            css: "img"
            type: Attribute
            attribute: width';

        $selector = DOMSelector::fromYamlString($yaml_string)->extract('<img src="photo.jpg" width="80" height="80" />');

        $this->assertEquals(80, $selector['img']);

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

        $selector = DOMSelector::fromYamlString($yaml_string)->extract('<ul><li><strong>STRONG!</strong></li></ul>');

        $this->assertEquals('<strong>STRONG!</strong>', $selector['content']);
        $this->assertEquals('STRONG!', $selector['text']);
    }

    public function testTypeImage()
    {
        $yaml_string = '
        img:
            css: "img"
            type: Image';

        $selector = DOMSelector::fromYamlString($yaml_string)->extract('<img src="photo.jpg" width="80" height="80" />');

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

        $selector = DOMSelector::fromYamlString($yaml_string)->extract('<div><a href="https://example.com/">Click Here!</a> </div>');

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

        $this->assertEquals(false, $selector['content']);
    }

    public function testDefaultItemType()
    {
        $yaml_string = '
        content:
            css: "div"';

        $selector = DOMSelector::fromYamlString($yaml_string)->extract('<div><h1><a href="https://example.com/">Click Here!</a></h1></div>');

        $this->assertEquals('Click Here!', $selector['content']);
    }
}