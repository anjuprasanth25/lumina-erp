<?php

namespace App\Mail;

use App\Models\ExpenseClaim;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;

class ExpenseClaimSubmittedMail extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public ExpenseClaim $claim;
    public String $action;


    public function __construct(ExpenseClaim $claim, String $action)
    {
        // Load relationships to ensure view has access to line items and currencies
        $this->claim = $claim->loadMissing([
            'employee',
            'currency',
            'items.category',
            'items.currency',
        ]);

        $this->action = $action;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match ($this->action) {
            'resubmitted' => "Expense Claim Resubmitted #{$this->claim->claim_number}",
            'updated'     => "Expense Claim Updated #{$this->claim->claim_number}",
            default       => "Expense Claim Submitted #{$this->claim->claim_number}",
        };
        return new Envelope(

            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.expense_claims.submitted',
            with: [
                'claim'        => $this->claim,
                'employeeName' => $this->claim->employee->name ?? 'Team Member',
                'viewUrl'      => route('expense-claim.show', $this->claim->id),
                'action'     => $this->action
            ],
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
