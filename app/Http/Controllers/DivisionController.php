<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Http;

class DivisionController extends Controller
{
    private string $division_endpoint = '/api/v1/divisions/';

    private string $cacheKey = 'aod_content_';

    /**
     * Show all divisions
     */
    public function index(): View
    {
        return view('division.index');
    }

    /**
     * Show an individual division
     */
    public function show($division): View
    {
        if (!view()->exists("division.content.{$division}")) {
            abort(404, 'Page not found');
        }

        try {
            $data = Http::withToken(config('services.aod.access_token'))
                ->acceptJson()
                ->get(config('services.aod.tracker_url')
                    . $this->division_endpoint
                    . 'bf'
//                  . $division
                )->json('data');

            if (empty($data)) {
                abort(404, 'Division request failed');
            }

            cache()->remember(
                "{$this->cacheKey}{$division}",
                config('app.cache_length'),
                fn() => $data['division']
            );

        } catch (\Exception $exception) {
            \Log::error($exception->getMessage());
            abort(404, 'Division request failed');
        }

        return view('division.show', ['data' => cache()->get("{$this->cacheKey}{$division}")])
            ->with('division');
    }

}
