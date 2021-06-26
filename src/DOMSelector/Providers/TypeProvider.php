<?php

declare(strict_types=1);

namespace DOMSelector\Providers;

use DOMSelector\Contracts\TypeInterface;
use DOMSelector\Exceptions\InvalidTypeException;
use DOMSelector\Types\AttributeType;
use DOMSelector\Types\HtmlType;
use DOMSelector\Types\ImageType;
use DOMSelector\Types\LinkType;
use DOMSelector\Types\TextType;

/**
 * Class TypeProvider.
 */
class TypeProvider
{
    /**
     * The type mappings for the selector.
     *
     * @var array
     */
    protected $mappings = [
        AttributeType::class,
        HtmlType::class,
        ImageType::class,
        LinkType::class,
        TextType::class,
    ];

    /**
     * Current available types.
     *
     * @var array
     */
    private $types = [];

    /**
     * TypeProvider constructor.
     *
     * @throws InvalidTypeException
     */
    public function __construct()
    {
        $this->boot();
    }

    /**
     * Bootstrap TypeProvider.
     *
     * @throws InvalidTypeException
     */
    public function boot()
    {
        foreach ($this->mappings as $type) {
            $this->add($type);
        }
    }

    /**
     * Get the types.
     *
     * @return array
     */
    public function types(): array
    {
        return $this->types;
    }

    /**
     * Add a new type to collection.
     *
     * @param string $type
     *
     * @throws InvalidTypeException
     *
     * @return void
     */
    public function add(string $type): void
    {
        if (!class_exists($type)) {
            throw new InvalidTypeException($type.' not found!');
        }

        $typeClass = new $type();

        if (!$typeClass instanceof TypeInterface) {
            throw new InvalidTypeException($type.' is not instance of TypeInterface.');
        }

        $names = explode('\\', $type);
        $this->types[end($names)] = $typeClass;
    }

    /**
     * Get the specific type.
     *
     * @param string $type
     *
     * @return TypeInterface|false
     */
    public function getType(string $type)
    {
        return $this->types[$type.'Type'] ?? false;
    }
}
