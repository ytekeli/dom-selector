<?php

declare(strict_types=1);

namespace DOMSelector\Types;

use DOMSelector\Contracts\TypeInterface;
use PHPHtmlParser\Dom\Node\HtmlNode;

class AttributeType implements TypeInterface
{
    /**
     * @inheritDoc
     */
    public static function getContent(HtmlNode $element, string $attribute = null)
    {
        return is_null($attribute) ? null : $element->getAttribute($attribute);
    }
}
