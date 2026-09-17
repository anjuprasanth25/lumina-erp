<?

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Header
            'employee_id'                  => ['required', 'exists:employees,id'],
            'company_id'                   => ['required', 'exists:companies,id'],
            'department_id'                => ['nullable', 'exists:departments,id'],
            'currency_id'                  => ['required', 'exists:currencies,id'],
            'expense_type_id'              => ['nullable', 'exists:expense_types,id'],
            'claim_date'                   => ['required', 'date'],
            'is_billed_to_client'          => ['boolean'],
            'client_name'                  => ['nullable', 'required_if:is_billed_to_client,true', 'string', 'max:255'],
            'has_policy_exception'         => ['boolean'],
            'exception_reason'             => ['nullable', 'required_if:has_policy_exception,true', 'string'],

            // Dynamic Line Items Array
            'items'                        => ['required', 'array', 'min:1'],
            'items.*.expense_category_id'  => ['required', 'exists:expense_categories,id'],
            'items.*.bill_date'            => ['required', 'date'],
            'items.*.invoice_number'       => ['nullable', 'string', 'max:255'],
            'items.*.supplier_client_name' => ['nullable', 'string', 'max:255'],
            'items.*.attendee_employee_names' => ['nullable', 'string', 'max:255'],
            'items.*.description'          => ['nullable', 'string'],
            'items.*.charged_to_type'      => ['nullable', 'string', 'max:255'],
            'items.*.charged_to_id'        => ['nullable', 'string', 'max:255'],
            'items.*.item_currency_id'     => ['required', 'exists:currencies,id'],
            'items.*.exchange_rate'        => ['required', 'numeric', 'min:0.000001'],
            'items.*.amount'               => ['required', 'numeric', 'min:0'],
            'items.*.vat_amount'           => ['nullable', 'numeric', 'min:0'],
            'items.*.attachment'           => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'], // 5MB max
        ];
    }
}
