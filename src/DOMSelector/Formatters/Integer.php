<?php
namespace DOMSelector\Formatters;

use DOMSelector\Contracts\FormatterInterface;

class Integer implements FormatterInterface
{
    /**
     * Formatting text to integer
     *
     * @param mixed $value
     * @return int
     */
    public function format($value): int
    {
        return (int)$value;
    }

    /**
     * Get formatter name
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Integer';
    }
}