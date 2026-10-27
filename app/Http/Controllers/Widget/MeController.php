<?php

namespace App\Http\Controllers\Widget;

use App\Http\Controllers\Controller;
use App\Models\Reviewer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** What the widget needs on boot once it has a token: who's reviewing, and how to look. */
class MeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        /** @var Reviewer $reviewer */
        $reviewer = $request->attributes->get('reviewer');

        return response()->json([
            'reviewer' => self::reviewer($reviewer),
            'site' => [
                'name' => $reviewer->site->name,
                'settings' => [
                    'position' => 'bottom-right',
                    'color' => null,
                    ...($reviewer->site->settings ?? []),
                ],
            ],
        ]);
    }

    public static function reviewer(Reviewer $reviewer): array
    {
        return [
            'id' => $reviewer->id,
            'name' => $reviewer->name,
            'email' => $reviewer->email,
        ];
    }
}
