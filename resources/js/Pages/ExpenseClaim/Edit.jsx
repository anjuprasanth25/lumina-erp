import React, { useMemo } from "react";
import { Head, Link, useForm } from "@inertiajs/react";
import AuthenticatedLayout from "../../Components/AuthenticatedLayout";
import TextInput from "../../Components/TextInput";
import TextareaInput from "../../Components/TextareaInput";
import SelectInput from "../../Components/SelectInput";
import { ArrowLeft, Paperclip } from "lucide-react";
import { route } from "ziggy-js";

export default function Edit({ auth, claim, lookups = {} }) {
    const {
        currencies = [],
        expenseTypes = [],
        canSubmitForOthers = false,
        categories = [],
        chargedTo = [],
        departments = [],
        projects = [],
    } = lookups;

    const formatDateForInput = (dateString) => {
        if (!dateString) return "";
        return new Date(dateString).toISOString().split("T")[0];
    };

    const { data, setData, put, processing, errors } = useForm({
        claim_date: formatDateForInput(claim.claim_date || ""),
        currency_id: claim.currency_id || "",
        expense_type_id: claim.expense_type_id || "",
        is_billed_to_client: Boolean(claim?.is_billed_to_client),
        has_policy_exception: Boolean(claim?.has_policy_exception),
        client_name: claim.client_name || "",
        exception_reason: claim.exception_reason || "",
        items:
            claim?.items.length === 0
                ? [
                      {
                          id: `item-${Date.now()}-${Math.random()}`,
                          expense_category_id: "",
                          bill_date: new Date().toISOString().split("T")[0],
                          invoice_number: "",
                          supplier_client_name: "",
                          attendee_employee_names: "",
                          description: "",
                          charged_to_type: "",
                          charged_to_id: "",
                          item_currency_id: "",
                          exchange_rate: 1.0,
                          amount: 0.0,
                          vat_amount: 0.0,
                          attachment: null,
                      },
                  ]
                : claim.items.map((item) => ({
                      ...item,
                      bill_date: formatDateForInput(item.bill_date),
                  })),
    });

    const handleSubmit = (e) => {
        e.preventDefault();

        put(route("expense-claim.update", claim.id));
    };

    const addItemRow = () => {
        const newrow = {
            id: `item-${Date.now()}-${Math.random()}`,
            expense_category_id: "",
            bill_date: new Date().toISOString().split("T")[0],
            invoice_number: "",
            supplier_client_name: "",
            attendee_employee_names: "",
            description: "",
            charged_to_type: "",
            charged_to_id: "",
            item_currency_id: "",
            exchange_rate: 1.0,
            amount: 0.0,
            vat_amount: 0.0,
            attachment: null,
        };
        setData("items", [...data.items, newrow]);
    };

    const removeItemRow = (index) => {
        if (data.items.length === 1) {
            alert("At least one expense line item is required.");
            return;
        }
        const updated = data.items.filter((item) => item.id !== index);
        setData("items", updated);
    };

    const handleItemChange = (itemId, field, value) => {
        const updatedItem = data.items.map((item) => {
            if (item.id === itemId) {
                return {
                    ...item,
                    [field]: value,
                };
            }
            return item;
        });
        setData("items", updatedItem);
    };

    const handleCurrencyOrDateChange = async (itemId, field, value) => {
        const updatedItem = data.items.map((item) => {
            if (item.id == itemId) {
                return {
                    ...item,
                    [field]: value,
                };
            }
            return item;
        });

        setData("items", updatedItem);

        const currentItem = updatedItem.find((item) => item.id === itemId);

        if (currentItem.item_currency_id && data.currency_id) {
            if (currentItem.item_currency_id === data.currency_id) {
                currentItem.exchange_rate = 1.0;
            } else {
                try {
                    const response = await axios.get(
                        route("expense-claim.exchange-rate"),
                        {
                            params: {
                                from_currency_id: currentItem.item_currency_id,
                                to_currency_id: data.currency_id,
                                date: currentItem.bill_date,
                            },
                        },
                    );
                    currentItem.exchange_rate = response.data.rate || 1.0;
                } catch (error) {
                    console.error("Failed to fetch exchange rate", error);
                }
            }
        }

        setData("items", [...updatedItem]);
    };

    const handleTypeChange = (itemId, value) => {
        const updatedItem = data.items.map((item) => {
            if (item.id === itemId) {
                return {
                    ...item,
                    charged_to_type: value,
                    charged_to_id: "",
                };
            }
            return item;
        });

        setData("items", updatedItem);
    };

    const getChargedToOptions = (chargedType) => {
        if (chargedType === "department") return departments || [];
        if (chargedType === "project") return projects || [];
        return [];
    };

    let currentCurrencyCode = data.currency_id || "";

    const baseCurrencyCode =
        currencies.find((c) => String(c.id) === String(currentCurrencyCode))
            ?.name || "Base Currency";

    const totals = useMemo(() => {
        return data.items.reduce(
            (acc, item) => {
                const rate = parseFloat(item.exchange_rate) || 1;
                const netAmount = (parseFloat(item.amount) || 0) * rate;
                const netVAT = (parseFloat(item.vat_amount) || 0) * rate;

                acc.netBase += netAmount;
                acc.vatBase += netVAT;
                acc.totalBase += netAmount + netVAT;

                return acc;
            },
            { netBase: 0, vatBase: 0, totalBase: 0 },
        );
    }, [data.items]);

    return (
        <AuthenticatedLayout auth={auth}>
            <Head title={`Edit Claim - ${claim.claim_number}`} />
            <div className="p-6 space-y-6 text-slate-200">
                <div className="flex justify-between items-center mb-6">
                    <h1 className="text-xl font-semibold tracking-wide text-white">
                        {`Edit Claim - ${claim.claim_number}`}
                        <p className="text-sm text-gray-400">
                            Status:
                            <span className="text-amber-400 font-semibold">
                                {claim.status}
                            </span>
                        </p>
                    </h1>
                    <Link
                        href={route("expense-claim.index")}
                        className="inline-flex items-center gap-1.5 text-sm font-medium text-slate-400 hover:text-white transition-colors"
                    >
                        <ArrowLeft className="w-4 h-4" />
                        <span>Back to Directory</span>
                    </Link>
                </div>
                {Object.keys(errors).length > 0 && (
                    <div className="mb-6 p-4 bg-red-950/40 border border-red-500/50 rounded-lg flex items-start gap-3">
                        <span className="text-red-500 font-bold text-lg leading-none">
                            ⚠️
                        </span>
                        <div>
                            <h4 className="text-red-400 font-semibold text-sm">
                                System Error
                            </h4>
                            <p className="text-gray-300 text-xs mt-1 leading-relaxed">
                                {errors.error ||
                                    "Please check the form inputs and try again."}
                            </p>
                        </div>
                    </div>
                )}

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="bg-slate-900/60 backdrop-blur-md border border-slate-800 rounded-xl p-6 shadow-xl">
                        <h2 className="text-lg font-medium text-slate-200 mb-4 border-b border-slate-700 pb-2">
                            Claim Overview
                        </h2>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-5">
                            {/* Employee */}
                            <TextInput
                                label="Employee"
                                value={claim?.employee?.name || auth.user.name}
                                disabled
                            />

                            {/* Claim Date */}
                            <TextInput
                                type="date"
                                label="Claim Date *"
                                value={data.claim_date}
                                onChange={(e) =>
                                    setData("claim_date", e.target.value)
                                }
                                error={errors.claim_date}
                            />

                            {/* Base Currency */}
                            <SelectInput
                                label="Base Currency *"
                                value={data.currency_id}
                                options={currencies}
                                onChange={(e) =>
                                    setData("currency_id", e.target.value)
                                }
                                disabled={!canSubmitForOthers}
                                error={errors.currency_id}
                            />

                            {/* Expense Type */}
                            <SelectInput
                                label="Expense Type *"
                                value={data.expense_type_id}
                                options={expenseTypes}
                                onChange={(e) =>
                                    setData("expense_type_id", e.target.value)
                                }
                                error={errors.expense_type_id}
                            />

                            {/* Billed To Client Toggle */}
                            <div className="flex items-center space-x-3 pt-6">
                                <input
                                    type="checkbox"
                                    id="is_billed_to_client"
                                    checked={data.is_billed_to_client}
                                    onChange={(e) =>
                                        setData(
                                            "is_billed_to_client",
                                            e.target.checked,
                                        )
                                    }
                                    className="w-4 h-4 rounded bg-slate-800 border-slate-700 text-blue-600 focus:ring-blue-500 focus:ring-offset-slate-900"
                                />
                                <label
                                    htmlFor="is_billed_to_client"
                                    className="text-sm font-medium text-slate-300 select-none"
                                >
                                    Billed To Client?
                                </label>
                            </div>

                            {/* Policy Exception Toggle */}
                            <div className="flex items-center space-x-3 pt-6">
                                <input
                                    type="checkbox"
                                    id="has_policy_exception"
                                    checked={data.has_policy_exception}
                                    onChange={(e) =>
                                        setData(
                                            "has_policy_exception",
                                            e.target.checked,
                                        )
                                    }
                                    className="w-4 h-4 rounded bg-slate-800 border-slate-700 text-amber-500 focus:ring-amber-500 focus:ring-offset-slate-900"
                                />
                                <label
                                    htmlFor="has_policy_exception"
                                    className="text-sm font-medium text-amber-400 select-none"
                                >
                                    Policy Exception Request?
                                </label>
                            </div>
                        </div>
                        {(data.is_billed_to_client ||
                            data.has_policy_exception) && (
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-5 pt-2 border-t border-slate-800/80">
                                {data.is_billed_to_client && (
                                    <TextInput
                                        label="Client Name*"
                                        value={data.client_name}
                                        placeholder="Enter client or project name"
                                        onChange={(e) =>
                                            setData(
                                                "client_name",
                                                e.target.value,
                                            )
                                        }
                                        error={errors.client_name}
                                    />
                                )}
                                {data.has_policy_exception && (
                                    <div
                                        className={
                                            data.is_billed_to_client
                                                ? "md:col-span-2"
                                                : "md:col-span-3"
                                        }
                                    >
                                        <label className="block text-xs font-semibold uppercase tracking-wider text-amber-400 mb-1">
                                            Exception Justification / Reason *
                                        </label>

                                        <TextareaInput
                                            id="reason"
                                            value={data.exception_reason}
                                            onChange={(e) =>
                                                setData(
                                                    "exception_reason",
                                                    e.target.value,
                                                )
                                            }
                                            rows={2} // Optional: default is 3 if omitted
                                            placeholder="Explain why this claim falls outside standard policy limits..."
                                        />
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                    <div className="bg-slate-900/60 backdrop-blur-md border border-slate-800 rounded-xl p-6 shadow-xl">
                        <div className="flex justify-between items-center border-b border-slate-700 pb-3">
                            <div>
                                <h2 className="text-lg font-medium text-slate-200">
                                    Expense Breakdown Items
                                </h2>
                                <p className="text-xs text-slate-400">
                                    Add detailed receipts and expense
                                    allocations.
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={addItemRow}
                                className="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-semibold tracking-wide transition flex items-center space-x-1"
                            >
                                <span>+ Add Line Item</span>
                            </button>
                        </div>
                        {data.items.map((item, index) => {
                            return (
                                <div
                                    key={item.id}
                                    className="bg-slate-950/40 border border-slate-800 rounded-lg p-4 space-y-4"
                                >
                                    <div className="flex justify-between items-center border-b border-slate-800 pb-2">
                                        <span className="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                            Line Item #{index + 1}
                                        </span>
                                        {data.items.length > 1 && (
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    removeItemRow(item.id)
                                                }
                                                className="text-gray-400 hover:text-red-400 p-1 rounded-lg transition-colors flex items-center gap-1 text-xs font-medium"
                                                title="Delete Item"
                                            >
                                                {/* Trash Icon (SVG) */}
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    className="h-4 w-4"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        strokeLinecap="round"
                                                        strokeLinejoin="round"
                                                        strokeWidth={2}
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                    />
                                                </svg>
                                            </button>
                                        )}
                                    </div>
                                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <SelectInput
                                            label="Category *"
                                            value={item.expense_category_id}
                                            options={categories}
                                            onChange={(e) =>
                                                handleItemChange(
                                                    item.id,
                                                    "expense_category_id",
                                                    e.target.value,
                                                )
                                            }
                                            error={
                                                errors[
                                                    `items.${index}.expense_category_id`
                                                ]
                                            }
                                        />

                                        <TextInput
                                            type="date"
                                            value={item.bill_date}
                                            label="Bill Date *"
                                            onChange={(e) =>
                                                handleCurrencyOrDateChange(
                                                    item.id,
                                                    "bill_date",
                                                    e.target.value,
                                                )
                                            }
                                            error={
                                                errors[
                                                    `items.${index}.bill_date`
                                                ]
                                            }
                                        />

                                        <TextInput
                                            type="text"
                                            label="Invoice / Receipt No. *"
                                            value={item.invoice_number}
                                            onChange={(e) =>
                                                handleItemChange(
                                                    item.id,
                                                    "invoice_number",
                                                    e.target.value,
                                                )
                                            }
                                            error={
                                                errors[
                                                    `items.${index}.invoice_number`
                                                ]
                                            }
                                        />

                                        <TextInput
                                            type="text"
                                            label="Merchant / Supplier *"
                                            value={item.supplier_client_name}
                                            onChange={(e) =>
                                                handleItemChange(
                                                    item.id,
                                                    "supplier_client_name",
                                                    e.target.value,
                                                )
                                            }
                                            error={
                                                errors[
                                                    `item.${index}.supplier_client_name`
                                                ]
                                            }
                                        />
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4 pt-2 border-t border-slate-800/40">
                                        <SelectInput
                                            label="Charge To Type"
                                            value={item.charged_to_type}
                                            options={chargedTo}
                                            onChange={(e) =>
                                                handleTypeChange(
                                                    item.id,
                                                    e.target.value,
                                                )
                                            }
                                            error={
                                                errors[
                                                    `items.${index}.charged_to_type`
                                                ]
                                            }
                                        />

                                        <SelectInput
                                            label={
                                                item.charged_to_type
                                                    ? `${item.charged_to_type.charAt(0).toUpperCase() + item.charged_to_type.slice(1)} Target`
                                                    : "Target Entity"
                                            }
                                            value={item.charged_to_id}
                                            options={getChargedToOptions(
                                                item.charged_to_type,
                                            )}
                                            onChange={(e) =>
                                                handleItemChange(
                                                    item.id,
                                                    "charged_to_id",
                                                    e.target.value,
                                                )
                                            }
                                            error={
                                                errors[
                                                    `items.${index}.charged_to_id`
                                                ]
                                            }
                                        />

                                        <SelectInput
                                            label="Item Currency *"
                                            value={item.item_currency_id}
                                            options={currencies}
                                            onChange={(e) =>
                                                handleCurrencyOrDateChange(
                                                    item.id,
                                                    "item_currency_id",
                                                    e.target.value,
                                                )
                                            }
                                        />

                                        <TextInput
                                            type="number"
                                            step="0.000001"
                                            label="Exchange Rate *"
                                            value={item.exchange_rate}
                                            onChange={(e) =>
                                                handleItemChange(
                                                    item.id,
                                                    "exchange_rate",
                                                    e.target.value,
                                                )
                                            }
                                            error={
                                                errors[
                                                    `items.${index}.exchange_rate`
                                                ]
                                            }
                                        />
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <TextInput
                                            type="number"
                                            step="0.01"
                                            label="Amount *"
                                            value={item.amount}
                                            onChange={(e) =>
                                                handleItemChange(
                                                    item.id,
                                                    "amount",
                                                    e.target.value,
                                                )
                                            }
                                            error={
                                                errors[`items.${index}.amount`]
                                            }
                                        />

                                        <TextInput
                                            type="number"
                                            step="0.01"
                                            label="VAT Amount"
                                            value={item.vat_amount}
                                            onChange={(e) =>
                                                handleItemChange(
                                                    item.id,
                                                    "vat_amount",
                                                    e.target.value,
                                                )
                                            }
                                            error={
                                                errors[
                                                    `items.${index}.vat_amount`
                                                ]
                                            }
                                        />

                                        <div>
                                            <label className="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">
                                                Receipt Attachment (PDF/Image) *
                                            </label>
                                            <input
                                                type="file"
                                                accept="image/*,application/pdf"
                                                onChange={(e) =>
                                                    handleItemChange(
                                                        item.id,
                                                        "attachment",
                                                        e.target.files[0],
                                                    )
                                                }
                                                className="block w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-200 hover:file:bg-slate-700 cursor-pointer"
                                            />
                                            {item.attachment_ref &&
                                                !item.attachment && (
                                                    <div className="mt-1.5 flex items-center gap-1.5 text-xs">
                                                        <span className="text-slate-400">
                                                            Current file:
                                                        </span>
                                                        <a
                                                            href={route(
                                                                "expense-claim.items.attachment",
                                                                item.id,
                                                            )}
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            className="inline-flex items-center gap-1 text-amber-400 hover:underline"
                                                        >
                                                            <Paperclip className="w-3 h-3" />
                                                            <span>
                                                                {
                                                                    item.attachment_ref
                                                                }
                                                            </span>
                                                        </a>
                                                    </div>
                                                )}
                                            {errors[
                                                `items.${index}.attachment`
                                            ] && (
                                                <p className="text-red-400 text-xs mt-1">
                                                    {
                                                        errors[
                                                            `items.${index}.attachment`
                                                        ]
                                                    }
                                                </p>
                                            )}
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <TextareaInput
                                            label="Attendees (Employees & Guests)"
                                            value={item.attendee_employee_names}
                                            onChange={(e) =>
                                                handleItemChange(
                                                    item.id,
                                                    "attendee_employee_names",
                                                    e.target.value,
                                                )
                                            }
                                            rows={2} // Optional: default is 3 if omitted
                                        />

                                        <TextareaInput
                                            label="Description"
                                            value={item.description}
                                            onChange={(e) =>
                                                handleItemChange(
                                                    item.id,
                                                    "description",
                                                    e.target.value,
                                                )
                                            }
                                            rows={2} // Optional: default is 3 if omitted
                                        />
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                    <div className="mt-6 bg-gray-800/60 rounded-lg p-5 border border-gray-700 flex flex-col md:flex-row justify-between items-center gap-4">
                        <div className="text-sm text-gray-400">
                            Total Items:{" "}
                            <span className="text-white font-semibold">
                                {data.items.length}
                            </span>
                        </div>
                        <div className="flex items-center gap-8 text-right">
                            <div>
                                <div className="text-xs text-gray-400">
                                    Net Amount ({baseCurrencyCode})
                                </div>
                                <div className="text-lg font-semibold text-gray-200">
                                    {totals.netBase.toFixed(2)}
                                </div>
                            </div>
                            <div>
                                <div className="text-xs text-gray-400">
                                    VAT Total ({baseCurrencyCode})
                                </div>
                                <div className="text-lg font-semibold text-gray-200">
                                    {totals.vatBase.toFixed(2)}
                                </div>
                            </div>
                            <div className="pl-4 border-l border-gray-700">
                                <div className="text-xs text-gray-400 font-medium">
                                    Claim Total ({baseCurrencyCode})
                                </div>
                                <div className="text-2xl font-bold text-emerald-400">
                                    {totals.totalBase.toFixed(2)}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div className="pt-8 mt-4 border-t border-gray-800 flex items-center justify-end gap-4">
                        {/* Cancel Button */}
                        <Link
                            href={route("expense-claim.index")} // ◄── Point this to your main employee directory route
                            className="px-5 py-2.5 rounded-lg text-sm font-medium text-gray-400 hover:text-white transition-colors"
                        >
                            Cancel
                        </Link>

                        {/* Submit Button */}
                        <button
                            type="submit"
                            disabled={processing}
                            className="px-6 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm transition-all shadow-md active:scale-95"
                        >
                            {processing
                                ? "Saving Record..."
                                : claim?.status === "rejected"
                                  ? "Resubmit Claim"
                                  : "Submit Claim"}
                        </button>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
