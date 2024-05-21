<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use App\Models\Reservation;

class ReservationVerified extends Mailable
{
    use Queueable, SerializesModels;

    /** The name of the one who has approved the reservation. */
    public string $approver;
    /** The reservation in question. */
    public Reservation $reservation;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $approver, Reservation $reservation)
    {
        $this->approver = $approver;
        $this->reservation = $reservation;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: __('reservations.reservation_verified'),
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            markdown: 'emails.reservation_verified'
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}