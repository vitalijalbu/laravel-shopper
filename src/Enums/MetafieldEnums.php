<?php

declare(strict_types=1);

namespace Shopper\Enums;

enum MetafieldType: string
{
    case SingleLineTextField = 'single_line_text_field';
    case MultiLineTextField = 'multi_line_text_field';
    case RichTextField = 'rich_text_field';
    case NumberInteger = 'number_integer';
    case NumberDecimal = 'number_decimal';
    case Date = 'date';
    case DateTime = 'date_time';
    case Boolean = 'boolean';
    case Color = 'color';
    case Weight = 'weight';
    case Volume = 'volume';
    case Dimension = 'dimension';
    case Rating = 'rating';
    case Url = 'url';
    case Json = 'json';
    case Money = 'money';
    case FileReference = 'file_reference';
    case ProductReference = 'product_reference';
    case VariantReference = 'variant_reference';
    case PageReference = 'page_reference';
    case CollectionReference = 'collection_reference';

    public function label(): string
    {
        return match ($this) {
            self::SingleLineTextField => 'Single line text',
            self::MultiLineTextField => 'Multi-line text',
            self::RichTextField => 'Rich text',
            self::NumberInteger => 'Integer',
            self::NumberDecimal => 'Decimal',
            self::Date => 'Date',
            self::DateTime => 'Date and time',
            self::Boolean => 'True or false',
            self::Color => 'Color',
            self::Weight => 'Weight',
            self::Volume => 'Volume',
            self::Dimension => 'Dimension',
            self::Rating => 'Rating',
            self::Url => 'URL',
            self::Json => 'JSON',
            self::Money => 'Money',
            self::FileReference => 'File',
            self::ProductReference => 'Product',
            self::VariantReference => 'Product variant',
            self::PageReference => 'Page',
            self::CollectionReference => 'Collection',
        };
    }

    public function isReference(): bool
    {
        return in_array($this, [
            self::FileReference,
            self::ProductReference,
            self::VariantReference,
            self::PageReference,
            self::CollectionReference,
        ]);
    }

    public function isNumber(): bool
    {
        return in_array($this, [self::NumberInteger, self::NumberDecimal, self::Money]);
    }

    public function isText(): bool
    {
        return in_array($this, [
            self::SingleLineTextField,
            self::MultiLineTextField,
            self::RichTextField,
        ]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

enum GiftCardStatus: string
{
    case Active = 'active';
    case Disabled = 'disabled';
    case Expired = 'expired';
    case Used = 'used';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Disabled => 'Disabled',
            self::Expired => 'Expired',
            self::Used => 'Fully Used',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'green',
            self::Disabled => 'gray',
            self::Expired => 'orange',
            self::Used => 'red',
        };
    }

    public function canBeUsed(): bool
    {
        return $this === self::Active;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

enum GiftCardTransactionType: string
{
    case Debit = 'debit';
    case Credit = 'credit';

    public function label(): string
    {
        return match ($this) {
            self::Debit => 'Used',
            self::Credit => 'Added',
        };
    }

    public function isUsage(): bool
    {
        return $this === self::Debit;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
