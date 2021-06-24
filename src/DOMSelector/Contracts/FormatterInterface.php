<?php

namespace DOMSelector\Contracts;

/**
 * Interface FormatterInterface
 * @package DOMSelector\Contracts
 */
interface FormatterInterface
{
    /**
     * Text formatting
     *
     * @param mixed $value
     * @return mixed
     */
    public function format($value);

    /**
     * Get formatter name
     *
     * @return mixed
     */
    public function getName();
}