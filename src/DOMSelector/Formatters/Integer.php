<?php

declare(strict_types=1);

namespace DOMSelector\Formatters;

use DOMSelector\Contracts\FormatterInterface;

class Integer implements FormatterInterface
{
    /**
     * Formatting text to integer.
     *
     * @param mixed $value
     */
    public function format($value): int
    {
        return (int) $value;
    }

    /**
     * Get formatter name.
     */
    public function getName(): string
    {
        return 'Integer';
    }
}
