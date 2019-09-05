<?php

namespace Phug\Test\Format;

use Phug\Formatter\Format\XmlFormat;

class FakeXmlFormat extends XmlFormat
{
    public function callFormatAttributeElement($attribute)
    {
        return parent::formatAttributeElement($attribute);
    }

    public function callFormatAttributeValueAccordingToName($value, $name, $checked = false)
    {
        return parent::formatAttributeValueAccordingToName($value, $name, $checked);
    }
}
