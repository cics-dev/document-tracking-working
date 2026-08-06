<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Document;

class DocumentStatusUpdate extends Mailable
{
    use Queueable, SerializesModels;

    public $document;
    public $recipientName;
    public $action;
    public $remarks;

    /**
     * Create a new message instance.
     */
    public function __construct(Document $document, $recipientName, $action = 'updated', $remarks = null)
    {
        $this->document = $document;
        $this->recipientName = $recipientName;
        $this->action = $action;
        $this->remarks = $remarks;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Document ' . ucfirst($this->action) . ': ' . $this->document->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.documents.status-update',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
