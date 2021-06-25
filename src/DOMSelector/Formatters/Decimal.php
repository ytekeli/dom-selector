<?php

declare(strict_types=1);

namespace DOMSelector\Formatters;

use DOMSelector\Contracts\FormatterInterface;

class Decimal implements FormatterInterface
{
    /**
     * Formatting text to integer.
     *
     * @param mixed $value
     */
    public function format($value): float
    {
        return (float) $value;
    }

    /**
     * Get formatter name.
     */
    public function getName(): string
    {
        return 'Decimal';
    }
}
