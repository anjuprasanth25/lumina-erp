<x-mail::message>
# Expense Claim Approved

Hello {{ $employeeName }},

Your expense claim **#{{ $claim->claim_number }}** has been successfully approved.

---

### **Claim Summary**
- **Reference Number:** {{ $claim->claim_number }}
- **Status:** <span style="color: #16a34a; font-weight: bold;">Approved</span>
- **Total Amount (Inc. VAT):** {{ $claim->currency->code ?? '' }}
{{ number_format($claim->total_amount_local_currency, 2) }}
@if($claim->is_billed_to_client)
- **Billed to Client:** Yes ({{ $claim->client_name }})
@endif

<x-mail::button :url="$viewUrl" color="primary">
View Claim Status
</x-mail::button>

Thanks,<br>
{{ config('app.name') }} System
</x-mail::message>

