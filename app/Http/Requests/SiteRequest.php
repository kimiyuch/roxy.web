<?php

namespace App\Http\Requests;

use App\Models\Site;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Creating or editing a site in the dashboard. Origins come one per line and may be full
 * URLs; the repository may be a GitHub URL. Both are tidied up before validation.
 */
class SiteRequest extends FormRequest
{
    /** Anyone signed in can add a site; editing needs the site to be in their account. */
    public function authorize(): bool
    {
        $site = $this->route('site');

        return ! $site instanceof Site || $this->user()->can('update', $site);
    }

    protected function prepareForValidation(): void
    {
        $origins = collect(preg_split('/[\s,]+/', (string) $this->input('origins', '')))
            ->filter()
            ->map(fn (string $origin) => str_contains($origin, '://*.') ? Str::lower(rtrim($origin, '/')) : (Site::originOf($origin) ?? $origin))
            ->unique()
            ->values()
            ->all();

        $repository = Str::of((string) $this->input('repository'))
            ->trim()
            ->replaceMatches('#^(https?://)?(www\.)?github\.com/#i', '')
            ->replaceMatches('#\.git$|/+$#', '')
            ->toString();

        $this->merge(['origins' => $origins, 'repository' => $repository ?: null]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'start_url' => ['required', 'url:http,https', 'max:2048'],
            'origins' => ['array', 'max:20'],
            'origins.*' => ['string', 'max:255', 'regex:#^https?://(\*\.)?[a-z0-9.-]+(:\d+)?$#'],
            'repository' => ['nullable', 'string', 'max:255', 'regex:#^[\w.-]+/[\w.-]+$#'],
            'position' => ['required', Rule::in(['bottom-right', 'bottom-left'])],
            'color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'origins.*.regex' => 'Addresses look like https://staging.example.ch (or https://*.example.ch for all its subdomains).',
            'repository.regex' => 'Use owner/repository, like kimiyuch/croc.web.',
        ];
    }

    /** The validated input as site attributes; the start URL's own address is always allowed. */
    public function siteAttributes(): array
    {
        return [
            'name' => $this->input('name'),
            'start_url' => $this->input('start_url'),
            'allowed_origins' => array_values(array_unique([Site::originOf($this->input('start_url')), ...$this->input('origins')])),
            'repository' => $this->input('repository'),
            'settings' => array_filter([
                'position' => $this->input('position'),
                'color' => $this->input('color') ? Str::lower($this->input('color')) : null,
            ]),
        ];
    }
}
