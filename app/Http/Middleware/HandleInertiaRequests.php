<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user()?->only('id', 'name', 'email'),
            ],
            // The sidebar: the account's sites with what's open on each.
            'sites' => fn () => $request->user()?->account->sites()
                ->withCount(['feedback as open_count' => fn ($query) => $query->where('status', 'open')])
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map->only('id', 'name', 'open_count'),
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
            ],
        ];
    }
}
