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

class ExpenseClaimApprovalMail extends Mailable
{
    use Queueable, SerializesModels;
    public ExpenseClaim $claim;
    public string $greeting;
    public string $title;

    /**
     * Create a new message instance.
     */
    public function __construct(ExpenseClaim $claim, String $greeting = 'Hello Approver', String $title = 'Expense Claim Pending Approval')
    {
        $this->claim = $claim->loadMissing([
            'employee',
            'currency',
            'items.category',
            'items.currency',
        ]);
        $this->greeting = $greeting;
        $this->title = $title;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $employeeName = $this->claim->employee->name ?? 'An Employee';
        return new Envelope(
            subject: "Action Required: Expense Claim Approval #{$this->claim->claim_number} ({$employeeName})",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.expense_claims.approval',
            with: [
                'claim'          => $this->claim,
                'employeeName'   => $this->claim->employee->name ?? 'N/A',
                'departmentName' => $this->claim->department->name ?? 'N/A',
                'approvalUrl'      => route('expense-claim.show', $this->claim->id),
                'verifierName' => $this->claim->verifier->name ?? 'System Admin',
                'title' => $this->title,
                'greeting' => $this->greeting
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
