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

class ExpenseClaimFullyApprovedMail extends Mailable
{
    use Queueable, SerializesModels;
    public ExpenseClaim $claim;
    /**
     * Create a new message instance.
     */
    public function __construct(ExpenseClaim $claim)
    {
        $this->claim = $claim->loadMissing([
            'employee',
            'currency',
            'items.category',
            'items.currency',
        ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $employeeName = $this->claim->employee->name ?? 'An Employee';
        return new Envelope(
            subject: "Expense Claim Approved #{$this->claim->claim_number} ({$employeeName})",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.expense_claims.approved',
            with: [
                'claim' => $this->claim,
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
