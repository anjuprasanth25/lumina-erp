<?php

namespace App\Mail;

use App\Models\ExpenseClaim;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExpenseClaimRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public ExpenseClaim $claim;
    public string $rejectedReason;
    public string $action;

    /**
     * Create a new message instance.
     */
    public function __construct(ExpenseClaim $claim, String $rejectedReason, String $action)
    {
        $this->claim = $claim->loadMissing([
            'employee:id,name',
            'currency:id,code',
            'items.category',
            'items.currency',
        ]);
        $this->rejectedReason = $rejectedReason;
        $this->action = $action;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Expense Claim Rejected #{$this->claim->claim_number}"
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.expense_claims.rejected',
            with: [
                'claim' => $this->claim,
                'reason' => $this->rejectedReason,
                'action' => $this->action,
                'employeeName' => $this->claim->employee->name ?? 'Team Member',
                'viewUrl'      => route('expense-claim.show', $this->claim->id),
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
