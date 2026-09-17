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
use phpDocumentor\Reflection\Types\Boolean;

class ExpenseClaimPendingVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public ExpenseClaim $claim;
    public String $action;

    /**
     * Create a new message instance.
     */
    public function __construct(ExpenseClaim $claim, String $action)
    {
        $this->claim = $claim->loadMissing([
            'employee',
            'department',
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
        $employeeName = $this->claim->employee->name ?? 'An Employee';
        return new Envelope(
            subject: "Action Required: Expense Claim Verification #{$this->claim->claim_number} ({$employeeName})",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.expense_claims.verification',
            with: [
                'claim'          => $this->claim,
                'employeeName'   => $this->claim->employee->name ?? 'N/A',
                'departmentName' => $this->claim->department->name ?? 'N/A',
                'verifyUrl'      => route('expense-claim.show', $this->claim->id),
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
