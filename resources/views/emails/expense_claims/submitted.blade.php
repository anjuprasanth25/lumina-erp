<x-mail::message>
# @if($action === 'resubmitted')
Expense Claim Resubmitted
@elseif($action === 'updated')
Expense Claim Updated
@else
Expense Claim Submitted
@endif

Hello {{ $employeeName }},


@if($action === 'resubmitted' || $action === 'updated')
Your expense claim **#{{ $claim->claim_number }}** has been updated and resubmitted for verification.
@else
Your expense claim **#{{ $claim->claim_number }}** has been successfully submitted on
**{{ \Carbon\Carbon::parse($claim->claim_date)->format('d M, Y') }}**.
@endif

---

### **Claim Summary**
- **Reference Number:** {{ $claim->claim_number }}
- **Status:** <span style="color: #d97706; font-weight: bold;">Pending Verification</span>
- **Total Amount (Inc. VAT):** {{ $claim->currency->code ?? '' }}
{{ number_format($claim->total_amount_local_currency, 2) }}
@if($claim->is_billed_to_client)
- **Billed to Client:** Yes ({{ $claim->client_name }})
@endif

---

### **Itemized Breakdown**

<x-mail::table>
| Date | Category | Merchant | Amount | VAT |
| :--- | :--- | :--- | :--- | :--- |
@foreach($claim->items as $item)
| {{ \Carbon\Carbon::parse($item->bill_date)->format('d/m/Y') }} | {{ $item->category->name ?? 'N/A' }} | {{ $item->supplier_client_name }} | {{ $item->currency->code ?? '' }} {{ number_format($item->amount, 2) }} |  {{ $item->currency->code ?? '' }} {{ number_format($item->vat_amount, 2) }}
@endforeach
</x-mail::table>

---

Your claim has been routed to our finance verification team. You will receive an automated email notification once
your claim passes verification and advances to approval.

<x-mail::button :url="$viewUrl" color="primary">
View Claim Status
</x-mail::button>

If you have any questions or need to make changes, please contact your Finance Administrator.

Thanks,<br>
{{ config('app.name') }} System
</x-mail::message>
