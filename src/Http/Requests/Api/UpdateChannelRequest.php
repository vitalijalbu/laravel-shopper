<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChannelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $channelId = $this->route('channel');
        
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', "unique:channels,slug,{$channelId}"],
            'url' => ['nullable', 'string', 'url'],
            'is_active' => ['boolean'],
            'is_default' => ['boolean'],
        ];
    }
}
