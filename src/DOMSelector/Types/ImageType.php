<?php

declare(strict_types=1);

namespace DOMSelector\Types;

use DOMSelector\Contracts\TypeInterface;
use PHPHtmlParser\Dom\Node\HtmlNode;

class ImageType implements TypeInterface
{
    /**
     * @inheritDoc
     */
    public static function getContent(HtmlNode $element, string $attribute = null)
    {
        return $element->getAttribute('src');
    }
}
