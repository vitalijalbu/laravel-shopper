<?php

declare(strict_types=1);

namespace Shopper\Http\Controllers\CP;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Shopper\Models\Channel;
use Shopper\Models\Site;

final class ChannelController
{
    public function index(Request $request, Site $site): Response
    {
        $query = Channel::query()
            ->where('site_id', $site->id);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $channels = $query->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('channels/index', [
            'site' => $site,
            'channels' => $channels,
        ]);
    }

    public function create(Site $site): Response
    {
        return Inertia::render('channels/form', [
            'site' => $site,
            'channel' => null,
            'availableLocales' => $this->getAvailableLocales(),
            'availableCurrencies' => $this->getAvailableCurrencies(),
        ]);
    }

    public function edit(Site $site, Channel $channel): Response
    {
        return Inertia::render('channels/form', [
            'site' => $site,
            'channel' => $channel,
            'availableLocales' => $this->getAvailableLocales(),
            'availableCurrencies' => $this->getAvailableCurrencies(),
        ]);
    }

    private function getAvailableLocales(): array
    {
        return [
            ['code' => 'en_US', 'name' => 'English (US)'],
            ['code' => 'en_GB', 'name' => 'English (UK)'],
            ['code' => 'it_IT', 'name' => 'Italian'],
            ['code' => 'fr_FR', 'name' => 'French'],
            ['code' => 'de_DE', 'name' => 'German'],
            ['code' => 'es_ES', 'name' => 'Spanish'],
            ['code' => 'pt_PT', 'name' => 'Portuguese'],
            ['code' => 'nl_NL', 'name' => 'Dutch'],
        ];
    }

    private function getAvailableCurrencies(): array
    {
        return require database_path('data/currencies.php');
    }
}
