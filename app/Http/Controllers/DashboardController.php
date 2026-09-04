<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

/**
 * DashboardController — Primary Application Gateway
 *
 * Redirects authenticated landing traffic immediately to the Daily Shift Handover Board
 * (`/daily`), establishing the shift board as the primary operating interface for SRE personnel.
 */
class DashboardController extends Controller
{
    /**
     * Redirect root dashboard requests to the Daily Activity Board.
     *
     * @return RedirectResponse Redirect to /daily
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('activities.daily');
    }
}
