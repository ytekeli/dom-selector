<?php

declare(strict_types=1);

namespace DOMSelector\Contracts;

use PHPHtmlParser\Dom\Node\HtmlNode;

interface TypeInterface
{
    /**
     * Get type content.
     *
     * @param HtmlNode    $element
     * @param string|null $attribute
     *
     * @return mixed
     */
    public static function getContent(HtmlNode $element, string $attribute = null);
}
