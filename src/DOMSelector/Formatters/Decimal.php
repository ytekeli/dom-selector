<?php

namespace DOMSelector\Formatters;

use DOMSelector\Contracts\FormatterInterface;

class Decimal implements FormatterInterface
{
    /**
     * Formatting text to integer.
     *
     * @param mixed $value
     *
     * @return float
     */
    public function format($value): float
    {
        return (float) $value;
    }

    /**
     * Get formatter name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Decimal';
    }
}
