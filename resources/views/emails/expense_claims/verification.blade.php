<x-mail::message>
# Expense Claim Pending Verification

Hello Verifier,

@if($action === 'resubmitted')
The expense claim **#{{ $claim->claim_number }}** has been updated and resubmitted by **{{ $claim->employee->name }}** following rejection.
@elseif($action === 'updated')
The expense claim **#{{ $claim->claim_number }}** submitted by **{{ $employeeName }}** ({{ $departmentName }}) has been **updated prior to
verification**. Please review the updated figures below.
@else
A new expense claim **#{{ $claim->claim_number }}** submitted by **{{ $claim->employee->name }}** is awaiting your verification.
@endif

---

### **Claim Overview**
- **Claim Ref:** {{ $claim->claim_number }}
- **Submitted By:** {{ $employeeName }}
- **Claim Date:** {{ \Carbon\Carbon::parse($claim->claim_date)->format('d M, Y') }}
- **Total Amount(Inc. VAT):** {{ $claim->currency->code ?? '' }}
{{ number_format($claim->total_amount_claim_currency, 2) }}
@if($claim->has_policy_exception)
- **Policy Exception:** <span style="color: #dc2626; font-weight: bold;">Yes</span> ({{ $claim->exception_reason }})
@endif

---

### **Items Included ({{ $claim->items->count() }})**

<x-mail::table>
| Date | Category | Merchant | Amount | VAT |
| :--- | :--- | :--- | :--- | :--- |
@foreach($claim->items as $item)
| {{ \Carbon\Carbon::parse($item->bill_date)->format('d/m/Y') }} | {{ $item->category->name ?? 'N/A' }} | {{ $item->supplier_client_name }} | {{ $item->currency->code ?? '' }} {{ number_format($item->amount, 2) }} |  {{ $item->currency->code ?? '' }} {{ number_format($item->vat_amount, 2) }}
@endforeach
</x-mail::table>

---

> **Note:** This claim has been sent to all eligible verifiers. Once verified by any verifier in the team, the
status will automatically update.

<x-mail::button :url="$verifyUrl" color="success">
Review & Verify Claim
</x-mail::button>

Thanks,<br>
{{ config('app.name') }} System
</x-mail::message>
