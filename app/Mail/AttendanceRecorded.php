<?php

namespace App\Mail;

use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AttendanceRecorded extends Mailable
{
    use Queueable, SerializesModels;

    public readonly string $time;

    public function __construct(
        public readonly Employee $employee,
        public readonly string $type
    ) {
        $this->time = now()->format('Y-m-d H:i:s');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Attendance {$this->type} Recorded"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.attendance_recorded'
        );
    }
}
