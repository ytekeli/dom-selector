<?php

declare(strict_types=1);

namespace DOMSelector;

use DOMSelector\Contracts\FormatterInterface;
use DOMSelector\Providers\TypeProvider;
use Exception;
use PHPHtmlParser\Dom;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

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
     * Selector DOM class
     *
     * @var Dom
     */
    private $dom;

    /**
     * Type Handler Class
     *
     * @var TypeProvider
     */
    protected $typeProvider;

    /**
     * DOMSelector constructor.
     *
     * @param array $config
     * @param array $formatters
     */
    public function __construct(array $config, array $formatters = [])
    {
        $this->config = $config;
        $this->dom = new Dom();
        $this->typeProvider = new TypeProvider();

        if (!empty($formatters)) {
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

        return new DOMSelector($config, $formatters);
    }

    /**
     * Create Extractor object from yaml file.
     */
    public static function fromYamlFile(string $yaml_file, array $formatters = []): DOMSelector
    {
        $config = \yaml_parse_file($yaml_file);

        return new DOMSelector($config, $formatters);
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
     * @return false|mixed|FormatterInterface
     */
    public function getFormatter(string $formatter)
    {
        return $this->formatters[$formatter] ?? false;
    }

    /**
     * Get formatters from config.
     *
     * @param string|array $items
     * @return array
     */
    protected function getFormettersFromConfig($items): array
    {
        $formatters = [];

        if (!is_array($items)) {
            $items = [$items];
        }

        foreach ($items as $item) {
            $formatter = $this->getFormatter($item);
            if ($formatter) {
                $formatters[$item] = $formatter;
            }
        }

        return $formatters;
    }

    /**
     * Extract config items from HTML string.
     *
     * @param string|Dom $html
     *
     * @return array
     */
    public function extract($html): array
    {
        if (!$html instanceof Dom) {
            $this->dom->loadStr($html);
        }

        $fields_data = [];

        foreach ($this->config as $field_name => $field_config) {
            $fields_data[$field_name] = $this->extractSelector($field_config, $this->dom);
        }

        return $fields_data;
    }

    /**
     * Extract config items from HTML file.
     *
     * @param string $file
     *
     * @throws Exception
     *
     * @return array
     */
    public function extractFromFile(string $file): array
    {
        try {
            $this->dom->loadFromFile($file);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return $this->extract($this->dom);
    }

    /**
     * Extract config items from url.
     *
     * @param string                     $url
     * @param ClientInterface|null|mixed $client
     *
     * @throws Exception|ClientExceptionInterface
     *
     * @return array
     */
    public function extractFromUrl(string $url, $client = null): array
    {
        try {
            $this->dom->loadFromUrl($url, null, $client);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return $this->extract($this->dom);
    }

    /**
     * Extract selector.
     *
     * @param array     $field_config
     * @param Dom|mixed $dom
     *
     * @return array|string|bool
     */
    protected function extractSelector(array $field_config, $dom)
    {
        try {
            $elements = $dom->find($field_config['css']);
        } catch (Exception $e) {
            $elements = [];
        }

        $item_type = $this->typeProvider->getType($field_config['type'] ?? '') ? $field_config['type'] : 'Text';

        $values = [];

        foreach ($elements as $element) {
            if (isset($field_config['children'])) {
                $value = $this->getChildItem($field_config, $element);
            } else {
                $formatters = $this->getFormettersFromConfig($field_config['format'] ?? []);

                $value = $this->extractField($element, $item_type, $field_config['attribute'] ?? null, $formatters);
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
     * @param mixed  $element
     * @param string $item_type
     * @param mixed  $attribute
     * @param array  $formatters
     *
     * @return false|mixed|string
     */
    protected function extractField($element, string $item_type, $attribute = null, array $formatters = [])
    {
        $content = false;

        $type = $this->typeProvider->getType($item_type);

        if ($type) {
            $content = $type->getContent($element, $attribute);
        }

        if (!empty($formatters) && $content) {
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
     * @param array $field_config
     * @param mixed $element
     *
     * @return array
     */
    public function getChildItem(array $field_config, $element): array
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
