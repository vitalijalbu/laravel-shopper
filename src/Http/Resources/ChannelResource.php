<?php

declare(strict_types=1);

namespace Shopper\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ChannelResource extends ResourceCollection
{
    public string $collects = ChannelResource::class;

    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
        ];
    }
}
