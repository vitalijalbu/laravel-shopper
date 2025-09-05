<?php

namespace Shopper\Enums;

enum StockNotificationStatus: string
{
    case PENDING = 'pending';
    case SENDING = 'sending';
    case SENT = 'sent';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('shopper::stock_notification.status.pending'),
            self::SENDING => __('shopper::stock_notification.status.sending'),
            self::SENT => __('shopper::stock_notification.status.sent'),
            self::FAILED => __('shopper::stock_notification.status.failed'),
            self::CANCELLED => __('shopper::stock_notification.status.cancelled'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'orange',
            self::SENDING => 'blue',
            self::SENT => 'green',
            self::FAILED => 'red',
            self::CANCELLED => 'gray',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
