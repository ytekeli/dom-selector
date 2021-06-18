<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use DOMSelector\DOMSelector;

class DOMSelectorTest extends TestCase
{
    public function testFromYamlString()
    {
        $yaml_string = file_get_contents('tests/data/files/basic_config.yaml');
        $selector = DOMSelector::fromYamlString($yaml_string);

        $config = $selector->getConfig();

        $this->assertEquals([
            'products' => [
                'css' => 'div.products a',
                'multiple' => true,
                'format' => 'Text',
            ]
        ], $config);
    }

    public function testFromYamlFile()
    {
        $selector = DOMSelector::fromYamlFile('tests/data/files/basic_config.yaml');

        $this->assertEquals([
            'products' => [
                'css' => 'div.products a',
                'multiple' => true,
                'format' => 'Text',
            ]
        ], $selector->getConfig());
    }
}