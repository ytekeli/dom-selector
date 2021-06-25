<?php

declare(strict_types=1);

namespace DOMSelector\Contracts;

/**
 * Interface FormatterInterface.
 */
interface FormatterInterface
{
    /**
     * Text formatting.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function format($value);

    /**
     * Get formatter name.
     *
     * @return mixed
     */
    public function getName();
}
