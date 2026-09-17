<x-mail::message>
# Expense Claim Verified

Hello {{ $employeeName }},

Your expense claim **#{{ $claim->claim_number }}** has been verified and sent for approval.

---

### **Claim Summary**
- **Reference Number:** {{ $claim->claim_number }}
- **Status:** Pending Approval
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

<x-mail::button :url="$viewUrl" color="primary">
View Claim Status
</x-mail::button>

Thanks,<br>
{{ config('app.name') }} System
</x-mail::message>
