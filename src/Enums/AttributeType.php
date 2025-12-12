<?php

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;

enum AttributeType: string
{
    use EnumSerializable;

    case TEXT = 'text';
    case NUMBER = 'number';
    case BOOLEAN = 'boolean';
    case SELECT = 'select';
    case MULTISELECT = 'multiselect';
    case COLOR = 'color';
    case IMAGE = 'image';

    public function label(): string
    {
        return match ($this) {
            self::TEXT => 'Text',
            self::NUMBER => 'Number',
            self::BOOLEAN => 'Boolean',
            self::SELECT => 'Select',
            self::MULTISELECT => 'Multi-select',
            self::COLOR => 'Color',
            self::IMAGE => 'Image',
        };
    }
}