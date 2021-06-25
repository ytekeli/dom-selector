<?php

declare(strict_types=1);

namespace DOMSelector;

use DOMSelector\Contracts\FormatterInterface;
use PHPHtmlParser\Dom;

/**
 * Class DOMSelector.
 */
class DOMSelector
{
    /**
     * @var array
     */
    private $config = [];

    /**
     * @var array
     */
    private $formatters = [];

    /**
     * DOMSelector constructor.
     *
     * @param $config
     * @param array $formatters
     */
    public function __construct($config, array $formatters = [])
    {
        $this->config = $config;

        if (! empty($formatters)) {
            foreach ($formatters as $formatter) {
                if ($formatter instanceof FormatterInterface) {
                    $this->formatters[$formatter->getName()] = $formatter;
                }
            }
        }
    }

    /**
     * Create Extractor object from yaml string.
     */
    public static function fromYamlString(string $yaml_string, array $formatters = []): DOMSelector
    {
        $config = \yaml_parse($yaml_string);

        return new static($config, $formatters);
    }

    /**
     * Create Extractor object from yaml file.
     */
    public static function fromYamlFile(string $yaml_file, array $formatters = []): DOMSelector
    {
        $config = \yaml_parse_file($yaml_file);

        return new static($config, $formatters);
    }

    /**
     * Get config.
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get all formatters.
     *
     * @return array
     */
    public function getFormatters(): array
    {
        return $this->formatters;
    }

    /**
     * Get specific formatter.
     *
     * @param string $formatter
     *
     * @return false|mixed|FormatterInterface
     */
    public function getFormatter(string $formatter)
    {
        return $this->formatters[$formatter] ?? false;
    }

    /**
     * Extract config items from HTML.
     *
     * @param string $html
     *
     * @throws
     *
     * @return array
     */
    public function extract(string $html): array
    {
        $dom = new Dom();

        $dom->loadStr($html);

        $fields_data = [];

        foreach ($this->config as $field_name => $field_config) {
            $fields_data[$field_name] = $this->extractSelector($field_config, $dom);
        }

        return $fields_data;
    }

    /**
     * Extract selector.
     *
     * @param array     $field_config
     * @param Dom|mixed $dom
     *
     * @return array|string
     */
    public function extractSelector(array $field_config, $dom)
    {
        $elements = [];

        try {
            $elements = $dom->find($field_config['css']);
        } catch (\Exception $e) {
        }

        if (count($elements) < 1) {
            return false;
        }

        if (!isset($field_config['type']) || !in_array($field_config['type'], ['Attribute', 'Html', 'Image', 'Link', 'Text'])) {
            $item_type = 'Text';
        } else {
            $item_type = $field_config['type'];
        }

        $values = [];

        foreach ($elements as $element) {
            if (isset($field_config['children'])) {
                $value = $this->getChildItem($field_config, $element);
            } else {
                $formatters = [];

                if (isset($field_config['format'])) {
                    if (!is_array($field_config['format'])) {
                        $field_config['format'] = [$field_config['format']];
                    }

                    foreach ($field_config['format'] as $f) {
                        if ($formatter = $this->getFormatter($f)) {
                            $formatters[$f] = $formatter;
                        }
                    }
                }

                $value = $this->extractField($element, $item_type, $field_config['attribute'] ?? false, $formatters);
            }

            if (isset($field_config['multiple']) && $field_config['multiple'] === true) {
                $values[] = $value;
            } else {
                return $value;
            }
        }

        return $values;
    }

    /**
     * Extract field.
     *
     * @param $element
     * @param $item_type
     * @param mixed $attribute
     *
     * @return false|mixed|string
     */
    public function extractField($element, $item_type, $attribute = false, array $formatters = [])
    {
        $content = false;

        if ($item_type == 'Attribute') {
            $content = $element->getAttribute($attribute);
        } elseif ($item_type == 'Html') {
            $content = $element->innerHtml;
        } elseif ($item_type == 'Image') {
            $content = $element->getAttribute('src');
        } elseif ($item_type == 'Link') {
            $content = $element->getAttribute('href');
        } elseif ($item_type == 'Text') {
            $content = trim(strip_tags($element->innerHtml));
        }

        if (! empty($formatters)) {
            /** @var FormatterInterface $formatter */
            foreach ($formatters as $formatter) {
                $content = $formatter->format($content);
            }
        }

        return $content;
    }

    /**
     * Get child item.
     *
     * @param $field_config
     * @param $element
     *
     * @return array
     */
    public function getChildItem($field_config, $element): array
    {
        $child_config = $field_config['children'];
        $child_item = [];

        foreach ($child_config as $config_name => $config_fields) {
            $child_value = $this->extractSelector($config_fields, $element);
            $child_item[$config_name] = $child_value;
        }

        return $child_item;
    }
}
