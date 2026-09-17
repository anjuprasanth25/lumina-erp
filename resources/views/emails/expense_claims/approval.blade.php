<x-mail::message>
# {{ $title }}

{{ $greeting }},

An expense claim has been submitted by **{{ $employeeName }}** ({{ $departmentName }}) and is awaiting approval
after verification.


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
- **Verified By:** {{ $verifierName }}

---

### **Items Included ({{ $claim->items->count() }})**

<x-mail::table>
| Date | Category | Merchant | Amount | VAT |
| :--- | :--- | :--- | :--- | :--- |
@foreach($claim->items as $item)
| {{ \Carbon\Carbon::parse($item->bill_date)->format('d/m/Y') }} | {{ $item->category->name ?? 'N/A' }} | {{ $item->supplier_client_name }} | {{ $item->currency->code ?? '' }} {{ number_format($item->amount, 2) }} |  {{ $item->currency->code ?? '' }} {{ number_format($item->vat_amount, 2) }}
@endforeach
</x-mail::table>

<x-mail::button :url="$approvalUrl" color="success">
Review & Approve Claim
</x-mail::button>

Thanks,<br>
{{ config('app.name') }} System
</x-mail::message>
