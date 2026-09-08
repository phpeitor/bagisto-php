<?php

namespace Webkul\Shop\Mail;

use App\Models\ComplaintBookEntry;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ComplaintBookResolved extends Mailable
{
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(public ComplaintBookEntry $complaint) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            to: [
                new Address(
                    $this->complaint->email,
                    trim($this->complaint->first_name.' '.$this->complaint->last_name)
                ),
            ],
            subject: trans('shop::app.emails.complaint-book.resolved-subject', ['correlative' => $this->complaint->correlative]),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'shop::emails.complaint-book-resolved',
        );
    }
}
