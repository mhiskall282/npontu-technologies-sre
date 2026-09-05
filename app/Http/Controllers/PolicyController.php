<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class PolicyController extends Controller
{
    /**
     * Render the Npontu SRE Operational Data & Privacy Policy.
     */
    public function privacy(): View
    {
        return view('policies.privacy');
    }

    /**
     * Render the SRE Operations Acceptable Use & Terms of Service.
     */
    public function terms(): View
    {
        return view('policies.terms');
    }

    /**
     * Render the Information Security & Cryptographic Audit Standard.
     */
    public function security(): View
    {
        return view('policies.security');
    }

    /**
     * Render the 99.98% Service Level Agreement & Incident Escalation Matrix.
     */
    public function sla(): View
    {
        return view('policies.sla');
    }
}
