<?php

namespace Shopper\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    protected $message = 'Insufficient stock available.';
}
