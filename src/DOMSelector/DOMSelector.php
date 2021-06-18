<?php

declare(strict_types=1);

namespace DOMSelector;

/**
 * Class DOMSelector
 * @package DOMSelector
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
     * @param $config
     * @param array $formatters
     */
    public function __construct($config, array $formatters = [])
    {
        $this->config = $config;

        if ($formatters) {
            //
        }
    }

    /**
     * Create Extractor object from yaml string
     *
     * @param string $yaml_string
     * @param array $formatters
     * @return DOMSelector
     */
    public static function fromYamlString(string $yaml_string, array $formatters = []): DOMSelector
    {
        $config = yaml_parse($yaml_string);

        return new static($config, $formatters);
    }

    /**
     * Create Extractor object from yaml file
     *
     * @param string $yaml_file
     * @param array $formatters
     * @return DOMSelector
     */
    public static function fromYamlFile(string $yaml_file, array $formatters = []): DOMSelector
    {
        $config = yaml_parse_file($yaml_file);

        return new static($config, $formatters);
    }

    /**
     * Get config
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}