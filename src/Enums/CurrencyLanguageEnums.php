<?php

namespace Shopper\Enums;

enum CurrencyType: string
{
    case FIAT = 'fiat';
    case CRYPTO = 'crypto';
    case COMMODITY = 'commodity';
    case DIGITAL = 'digital';

    public function label(): string
    {
        return match ($this) {
            self::FIAT => 'Fiat Currency',
            self::CRYPTO => 'Cryptocurrency',
            self::COMMODITY => 'Commodity',
            self::DIGITAL => 'Digital Currency',
        };
    }
}

enum RegulatoryStatus: string
{
    case APPROVED = 'approved';
    case RESTRICTED = 'restricted';
    case BANNED = 'banned';
    case UNREGULATED = 'unregulated';

    public function label(): string
    {
        return match ($this) {
            self::APPROVED => 'Approved',
            self::RESTRICTED => 'Restricted',
            self::BANNED => 'Banned',
            self::UNREGULATED => 'Unregulated',
        };
    }
}

enum CurrencyStatus: string
{
    case ACTIVE = 'active';
    case DEPRECATED = 'deprecated';
    case RESTRICTED = 'restricted';
    case DELISTED = 'delisted';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::DEPRECATED => 'Deprecated',
            self::RESTRICTED => 'Restricted',
            self::DELISTED => 'Delisted',
        };
    }

    public function isUsable(): bool
    {
        return $this === self::ACTIVE;
    }
}

enum ExchangeRateSource: string
{
    case ECB = 'ecb';
    case YAHOO = 'yahoo';
    case COINBASE = 'coinbase';
    case BINANCE = 'binance';
    case FIXER = 'fixer';
    case OPENEXCHANGE = 'openexchange';
    case AI_PREDICTION = 'ai_prediction';

    public function label(): string
    {
        return match ($this) {
            self::ECB => 'European Central Bank',
            self::YAHOO => 'Yahoo Finance',
            self::COINBASE => 'Coinbase',
            self::BINANCE => 'Binance',
            self::FIXER => 'Fixer.io',
            self::OPENEXCHANGE => 'Open Exchange Rates',
            self::AI_PREDICTION => 'AI Prediction',
        };
    }

    public function isReliable(): bool
    {
        return in_array($this, [self::ECB, self::COINBASE, self::BINANCE]);
    }
}

enum PriceTrend: string
{
    case STRONG_UP = 'strong_up';
    case UP = 'up';
    case STABLE = 'stable';
    case DOWN = 'down';
    case STRONG_DOWN = 'strong_down';

    public function label(): string
    {
        return match ($this) {
            self::STRONG_UP => 'Strong Upward',
            self::UP => 'Upward',
            self::STABLE => 'Stable',
            self::DOWN => 'Downward',
            self::STRONG_DOWN => 'Strong Downward',
        };
    }

    public function direction(): int
    {
        return match ($this) {
            self::STRONG_UP => 2,
            self::UP => 1,
            self::STABLE => 0,
            self::DOWN => -1,
            self::STRONG_DOWN => -2,
        };
    }
}

enum PricingStrategy: string
{
    case AUTO_CONVERT = 'auto_convert';
    case MANUAL_OVERRIDE = 'manual_override';
    case REGIONAL_PRICING = 'regional_pricing';

    public function label(): string
    {
        return match ($this) {
            self::AUTO_CONVERT => 'Auto Convert',
            self::MANUAL_OVERRIDE => 'Manual Override',
            self::REGIONAL_PRICING => 'Regional Pricing',
        };
    }
}

enum TextDirection: string
{
    case LTR = 'ltr';
    case RTL = 'rtl';

    public function label(): string
    {
        return match ($this) {
            self::LTR => 'Left to Right',
            self::RTL => 'Right to Left',
        };
    }
}

enum LanguageComplexity: string
{
    case SIMPLE = 'simple';
    case MODERATE = 'moderate';
    case COMPLEX = 'complex';

    public function label(): string
    {
        return match ($this) {
            self::SIMPLE => 'Simple',
            self::MODERATE => 'Moderate',
            self::COMPLEX => 'Complex',
        };
    }

    public function translationMultiplier(): float
    {
        return match ($this) {
            self::SIMPLE => 1.0,
            self::MODERATE => 1.5,
            self::COMPLEX => 2.0,
        };
    }
}

enum BusinessPriority: string
{
    case HIGH = 'high';
    case MEDIUM = 'medium';
    case LOW = 'low';

    public function label(): string
    {
        return match ($this) {
            self::HIGH => 'High Priority',
            self::MEDIUM => 'Medium Priority',
            self::LOW => 'Low Priority',
        };
    }

    public function weight(): int
    {
        return match ($this) {
            self::HIGH => 3,
            self::MEDIUM => 2,
            self::LOW => 1,
        };
    }
}

enum LanguageStatus: string
{
    case ACTIVE = 'active';
    case BETA = 'beta';
    case DEPRECATED = 'deprecated';
    case UNSUPPORTED = 'unsupported';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::BETA => 'Beta',
            self::DEPRECATED => 'Deprecated',
            self::UNSUPPORTED => 'Unsupported',
        };
    }

    public function isUsable(): bool
    {
        return in_array($this, [self::ACTIVE, self::BETA]);
    }
}

enum TranslationType: string
{
    case STRING = 'string';
    case PLURAL = 'plural';
    case RICH_TEXT = 'rich_text';
    case MARKDOWN = 'markdown';
    case HTML = 'html';

    public function label(): string
    {
        return match ($this) {
            self::STRING => 'String',
            self::PLURAL => 'Plural',
            self::RICH_TEXT => 'Rich Text',
            self::MARKDOWN => 'Markdown',
            self::HTML => 'HTML',
        };
    }
}

enum QualityGrade: string
{
    case A = 'A';
    case B = 'B';
    case C = 'C';
    case D = 'D';
    case F = 'F';

    public function label(): string
    {
        return match ($this) {
            self::A => 'Excellent',
            self::B => 'Good',
            self::C => 'Average',
            self::D => 'Poor',
            self::F => 'Failed',
        };
    }

    public function score(): int
    {
        return match ($this) {
            self::A => 90,
            self::B => 80,
            self::C => 70,
            self::D => 60,
            self::F => 50,
        };
    }
}

enum PricingMethod: string
{
    case AUTO_CONVERT = 'auto_convert';
    case MANUAL = 'manual';
    case RULE_BASED = 'rule_based';
    case COMPETITIVE = 'competitive';

    public function label(): string
    {
        return match ($this) {
            self::AUTO_CONVERT => 'Auto Convert',
            self::MANUAL => 'Manual',
            self::RULE_BASED => 'Rule Based',
            self::COMPETITIVE => 'Competitive',
        };
    }
}

enum PricePosition: string
{
    case BELOW_MARKET = 'below_market';
    case AT_MARKET = 'at_market';
    case ABOVE_MARKET = 'above_market';

    public function label(): string
    {
        return match ($this) {
            self::BELOW_MARKET => 'Below Market',
            self::AT_MARKET => 'At Market',
            self::ABOVE_MARKET => 'Above Market',
        };
    }
}
