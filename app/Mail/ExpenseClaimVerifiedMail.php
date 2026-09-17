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

class ExpenseClaimVerifiedMail extends Mailable
{
    use Queueable, SerializesModels;

    public ExpenseClaim $claim;
    /**
     * Create a new message instance.
     */
    public function __construct(ExpenseClaim $claim)
    {
        $this->claim = $claim->loadMissing(
            [
                'employee:id,name,email',
                'verifier:id,name',
                'lineManager:id,name',
                'buApprover:id,name',
                'currency:id,code'
            ]
        );
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Expense Claim Verified: #' . $this->claim->claim_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {

        return new Content(
            markdown: 'emails.expense_claims.verified',
            with: [
                'claim'        => $this->claim,
                'employeeName' => $this->claim->employee->name ?? 'Team Member',
                'viewUrl'      => route('expense-claim.show', $this->claim->id)
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
