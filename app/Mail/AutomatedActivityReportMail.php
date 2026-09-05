<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AutomatedActivityReportMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new mailable instance.
     *
     * @param  array<string, mixed>  $metrics
     */
    public function __construct(
        public User $recipient,
        public string $period,
        public string $startDate,
        public string $endDate,
        public array $metrics,
        public Collection $activities,
        public Collection $handovers
    ) {}

    /**
     * Build the message.
     */
    public function build(): self
    {
        $periodTitle = match ($this->period) {
            'weekly' => 'Weekly Shift Performance & Compliance Digest',
            'monthly' => 'Monthly SRE Executive Reliability & SLA Report',
            default => 'Daily Operational Shift Digest',
        };

        $formattedWindow = ($this->startDate === $this->endDate)
            ? Carbon::parse($this->startDate)->format('d M Y')
            : Carbon::parse($this->startDate)->format('d M Y').' to '.Carbon::parse($this->endDate)->format('d M Y');

        $subject = "[Npontu SRE] {$periodTitle} ({$formattedWindow})";

        return $this->subject($subject)
            ->view('emails.automated_report');
    }
}
