<?php

declare(strict_types=1);

namespace DOMSelector\Types;

use DOMSelector\Contracts\TypeInterface;
use PHPHtmlParser\Dom\Node\HtmlNode;

class TextType implements TypeInterface
{
    /**
     * Get type content.
     *
     * @param HtmlNode    $element
     * @param string|null $attribute
     *
     * @return string
     */
    public static function getContent(HtmlNode $element, string $attribute = null): string
    {
        return trim(strip_tags($element->innerhtml));
    }
}
