import React, { useState } from "react";
import { Head, Link, router, usePage } from "@inertiajs/react";
import AuthenticatedLayout from "../../Components/AuthenticatedLayout";
import { ArrowLeft, CheckCircle2 } from "lucide-react";
import StatusBadge from "../../Components/StatusBadge";

export default function Show({ auth, claim, can = {} }) {
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [showRejectModal, setShowRejectModal] = useState(false);
    const [rejectionReason, setRejectionReason] = useState("");
    const [isBookingOpen, setIsBookingOpen] = useState(false);
    const [referenceCode, setReferenceCode] = useState("");
    const [isPostOpen, setIsPostOpen] = useState(false);
    const [paymentMethod, setPaymentMethod] = useState("Bank Transfer");
    const [isPosting, setIsPosting] = useState(false);
    const { flash } = usePage().props;
    const formatDate = (dateString) => {
        if (!dateString) return "-";
        return new Date(dateString).toLocaleDateString("en-GB", {
            day: "2-digit",
            month: "short",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        });
    };

    const ROUTE_MAP = {
        pending_verification: "expense-claim.verify",
        pending_approval: "expense-claim.approve",
        pending_finance_approval: "expense-claim.finance-approve",
    };

    const handleVerify = (e) => {
        e.preventDefault();
        if (
            confirm(
                "Are you sure you want to verify and forward this claim for approval?",
            )
        ) {
            setIsSubmitting(true);
            router.post(
                route("expense-claim.verify", claim.id),
                {
                    action: "approve",
                },
                {
                    onFinish: () => setIsSubmitting(false),
                },
            );
        }
    };

    const handleApprove = (e) => {
        e.preventDefault();

        const targetRoute = ROUTE_MAP[claim.status] || "expense-claim.verify";

        if (confirm("Are you sure you want to approve this claim?")) {
            setIsSubmitting(true);
            router.post(
                route(targetRoute, claim.id),
                {
                    action: "approve",
                },
                {
                    onFinish: () => setIsSubmitting(false),
                },
            );
        }
    };

    const handleReject = (e) => {
        e.preventDefault();
        if (!rejectionReason.trim()) return;

        const targetRoute = ROUTE_MAP[claim.status] || "expense-claim.verify";

        setIsSubmitting(true);
        router.post(
            route(targetRoute, claim.id),
            {
                action: "reject",
                rejection_reason: rejectionReason,
            },
            {
                onSuccess: () => {
                    setRejectionReason("");
                    setShowRejectModal(false);
                },
                onFinish: () => {
                    setIsSubmitting(false); // Always re-enable button
                },
            },
        );
    };

    const handleBookSubmit = (e) => {
        e.preventDefault();

        router.post(
            route("expense-claim.book", claim.id),
            {
                reference_code: referenceCode,
            },
            {
                onSuccess: () => {
                    setReferenceCode("");
                    setIsBookingOpen(false);
                },
                onFinish: () => {
                    setIsBookingOpen(false);
                },
            },
        );
    };

    const handlePostSubmit = (e) => {
        e.preventDefault();
        setIsPosting(true);

        router.post(
            route("expense-claim.post", claim.id),
            {
                payment_method: paymentMethod,
            },
            {
                onSuccess: () => {
                    setPaymentMethod("");
                    setIsPostOpen(false);
                },
                onFinish: () => {
                    setIsPosting(false);
                },
            },
        );
    };

    return (
        <AuthenticatedLayout auth={auth}>
            <Head title={`Claim - ${claim.claim_number}`} />
            <div className="p-6 space-y-6 text-slate-200">
                {/* Back Button & Header */}
                <div className="space-y-4 mb-6">
                    {/* Navigation Link */}
                    <div>
                        <Link
                            href={route("expense-claim.index")}
                            className="inline-flex items-center gap-1.5 text-sm font-medium text-slate-400 hover:text-white transition-colors"
                        >
                            <ArrowLeft className="w-4 h-4" />
                            <span>Back to Directory</span>
                        </Link>
                    </div>

                    {/* Flash Success Banner */}
                    {flash?.success && (
                        <div className="flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-lg px-4 py-3 text-sm font-medium shadow-sm">
                            <CheckCircle2 className="w-4 h-4 shrink-0" />
                            <span>{flash.success}</span>
                        </div>
                    )}
                </div>
                <div className="bg-[#111827] border border-slate-800 rounded-xl p-6 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div className="flex items-center space-x-3">
                            <h1 className="text-1xl font-bold text-white tracking-wide">
                                {claim.claim_number}
                            </h1>
                            <StatusBadge status={claim.status} />
                        </div>
                        <p className="text-sm text-slate-400 mt-2 flex items-center space-x-3">
                            <span>
                                Submitted:
                                {claim?.created_at
                                    ? new Date(
                                          claim.created_at,
                                      ).toLocaleDateString("en-GB", {
                                          day: "2-digit",
                                          month: "short",
                                          year: "numeric",
                                      })
                                    : "-"}
                            </span>
                            <span>•</span>
                            <span>
                                {claim?.items_count ||
                                    claim?.items?.length ||
                                    0}{" "}
                                Line Items
                            </span>
                        </p>
                    </div>
                    {/* Permission Action Buttons */}
                    <div className="flex flex-wrap items-center gap-2">
                        {can.update && (
                            <Link
                                href={route("expense-claim.edit", claim.id)}
                                className="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 rounded-lg text-sm font-medium transition"
                            >
                                Edit Claim
                            </Link>
                        )}
                        {can.verify &&
                            claim.status == "pending_verification" && (
                                <>
                                    <button
                                        type="button"
                                        disabled={isSubmitting}
                                        onClick={handleVerify}
                                        className="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-sm font-medium transition shadow-lg shadow-blue-600/20"
                                    >
                                        Verify & Send for Approval
                                    </button>
                                    <button
                                        type="button"
                                        disabled={isSubmitting}
                                        onClick={() => setShowRejectModal(true)}
                                        className="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-sm font-medium transition shadow-lg shadow-rose-600/20 disabled:opacity-50"
                                    >
                                        Reject
                                    </button>
                                </>
                            )}

                        {can.approve && claim.status === "pending_approval" && (
                            <>
                                <button
                                    type="button"
                                    disabled={isSubmitting}
                                    onClick={handleApprove}
                                    className="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-sm font-medium transition shadow-lg shadow-blue-600/20"
                                >
                                    Approve
                                </button>
                                <button
                                    type="button"
                                    disabled={isSubmitting}
                                    onClick={() => setShowRejectModal(true)}
                                    className="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-sm font-medium transition shadow-lg shadow-rose-600/20 disabled:opacity-50"
                                >
                                    Reject
                                </button>
                            </>
                        )}

                        {can.financeapprove &&
                            claim.status == "pending_finance_approval" && (
                                <>
                                    <button
                                        type="button"
                                        disabled={isSubmitting}
                                        onClick={handleApprove}
                                        className="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-sm font-medium transition shadow-lg shadow-blue-600/20"
                                    >
                                        Approve
                                    </button>
                                    <button
                                        type="button"
                                        disabled={isSubmitting}
                                        onClick={() => setShowRejectModal(true)}
                                        className="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-sm font-medium transition shadow-lg shadow-rose-600/20 disabled:opacity-50"
                                    >
                                        Reject
                                    </button>
                                </>
                            )}

                        {can.book && claim.status == "approved" && (
                            <>
                                <button
                                    onClick={() => setIsBookingOpen(true)}
                                    type="button"
                                    className="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-sm font-medium transition shadow-lg shadow-blue-600/20"
                                >
                                    BOOK
                                </button>
                            </>
                        )}

                        {can.post && claim.status == "booked" && (
                            <button
                                type="button"
                                onClick={() => setIsPostOpen(true)}
                                className="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-sm font-medium transition shadow-lg shadow-blue-600/20"
                            >
                                POST
                            </button>
                        )}

                        {showRejectModal && (
                            <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                                <div className="bg-slate-800 rounded-xl max-w-md w-full p-6 space-y-4 border border-slate-700 text-white">
                                    <h3 className="text-lg font-semibold">
                                        Reject Expense Claim
                                    </h3>
                                    <form
                                        onSubmit={handleReject}
                                        className="space-y-4"
                                    >
                                        <div>
                                            <label className="block text-xs font-medium text-slate-400 mb-1">
                                                Reason for Rejection{" "}
                                                <span className="text-rose-400">
                                                    *
                                                </span>
                                            </label>
                                            <textarea
                                                required
                                                rows="3"
                                                value={rejectionReason}
                                                onChange={(e) =>
                                                    setRejectionReason(
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="Provide details on why this claim is rejected..."
                                                className="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-sm text-slate-200 focus:ring-2 focus:ring-rose-500 focus:outline-none"
                                            />
                                        </div>
                                        <div className="flex justify-end gap-2">
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    setShowRejectModal(false)
                                                }
                                                className="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg text-sm"
                                            >
                                                Cancel
                                            </button>
                                            <button
                                                type="submit"
                                                disabled={
                                                    isSubmitting ||
                                                    !rejectionReason.trim()
                                                }
                                                className="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-sm font-medium disabled:opacity-50"
                                            >
                                                Confirm Rejection
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        )}

                        {/* Booking Modal */}
                        {isBookingOpen && (
                            <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
                                <div className="bg-slate-900 border border-slate-800 rounded-xl p-6 w-full max-w-md shadow-2xl">
                                    <h3 className="text-lg font-semibold text-white mb-4">
                                        Book Expense Claim
                                    </h3>

                                    <form
                                        onSubmit={handleBookSubmit}
                                        className="space-y-4"
                                    >
                                        <div>
                                            <label className="block text-sm font-medium text-slate-300 mb-1">
                                                Booking Reference Code{" "}
                                                <span className="text-rose-500">
                                                    *
                                                </span>
                                            </label>
                                            <input
                                                type="text"
                                                value={referenceCode}
                                                onChange={(e) =>
                                                    setReferenceCode(
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="e.g., JV-2026-0917"
                                                className="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500"
                                            />
                                        </div>

                                        <div className="flex justify-end gap-3 pt-2">
                                            <button
                                                type="button"
                                                onClick={() => {
                                                    setIsBookingOpen(false);
                                                }}
                                                className="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-sm font-medium transition"
                                            >
                                                Cancel
                                            </button>
                                            <button
                                                type="submit"
                                                disabled={isSubmitting}
                                                className="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-sm font-medium transition disabled:opacity-50"
                                            >
                                                Confirm Booking
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        )}

                        {isPostOpen && (
                            <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
                                <div className="bg-slate-900 border border-slate-800 rounded-xl p-6 w-full max-w-md shadow-2xl">
                                    <h3 className="text-lg font-semibold text-white mb-4">
                                        Post Expense Claim
                                    </h3>

                                    <form
                                        onSubmit={handlePostSubmit}
                                        className="space-y-4"
                                    >
                                        <div>
                                            <label className="block text-sm font-medium text-slate-300 mb-1">
                                                Payment Method{" "}
                                                <span className="text-rose-500">
                                                    *
                                                </span>
                                            </label>
                                            <select
                                                value={paymentMethod}
                                                onChange={(e) =>
                                                    setPaymentMethod(
                                                        e.target.value,
                                                    )
                                                }
                                                className="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-emerald-500"
                                                required
                                            >
                                                <option value="Bank Transfer">
                                                    Bank Transfer
                                                </option>
                                                <option value="Corporate Card">
                                                    Corporate Card
                                                </option>
                                                <option value="Petty Cash">
                                                    Petty Cash
                                                </option>
                                                <option value="Cheque">
                                                    Cheque
                                                </option>
                                            </select>
                                        </div>

                                        <div className="flex justify-end gap-3 pt-2">
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    setIsPostOpen(false)
                                                }
                                                className="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-sm font-medium transition"
                                            >
                                                Cancel
                                            </button>
                                            <button
                                                type="submit"
                                                disabled={isPosting}
                                                className="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-sm font-medium transition disabled:opacity-50"
                                            >
                                                {isPosting
                                                    ? "Posting..."
                                                    : "Confirm Post"}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div className="bg-[#111827] border border-slate-800 p-4 rounded-xl">
                        <p className="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                            Employee
                        </p>
                        <p className="text-sm font-semibold text-white mt-1">
                            {claim?.employee?.name || "-"}
                        </p>
                        <p className="text-sm text-slate-400">
                            {claim?.employee?.code || ""}
                        </p>
                    </div>
                    <div className="bg-[#111827] border border-slate-800 p-4 rounded-xl">
                        <p className="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                            Company & Dept
                        </p>
                        <p className="text-sm font-semibold text-white mt-1">
                            {claim?.company?.name || "-"}
                        </p>
                        <p className="text-sm text-slate-400">
                            {claim?.department?.name || ""}
                        </p>
                    </div>
                    <div className="bg-[#111827] border border-slate-800 p-4 rounded-xl">
                        <p className="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                            Approvers
                        </p>
                        <p className="text-xs text-slate-300 mt-1">
                            {claim?.line_manager ? "LM:" : "BU:"}{" "}
                            <span className="text-white">
                                {claim?.line_manager
                                    ? claim?.line_manager.name
                                    : claim?.bu_approver?.name}
                            </span>
                        </p>
                        <p className="text-xs text-slate-300 mt-1">
                            FM:{" "}
                            <span className="text-white">
                                {claim?.finance_approver?.name || "Unassigned"}
                            </span>
                        </p>
                    </div>
                    <div className="bg-[#111827] border border-slate-800 p-4 rounded-xl">
                        <p className="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                            Total Amount
                        </p>
                        <span>
                            {claim?.currency?.name || "AED"}{" "}
                            {Number(
                                claim?.total_amount_local_currency || 0,
                            ).toLocaleString("en-US", {
                                minimumFractionDigits: 2,
                            })}
                        </span>
                    </div>
                </div>

                {/* Dedicated Finance Information Card */}
                {can.financeview &&
                    (claim.booking_reference_code || claim.payment_method) && (
                        <div className="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-lg mb-6">
                            <h4 className="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">
                                Finance & Accounting Details
                            </h4>

                            <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                                {claim.booking_reference_code && (
                                    <div>
                                        <span className="text-xs text-slate-500 block">
                                            Booking Reference
                                        </span>
                                        <span className="text-slate-200 font-mono font-medium">
                                            {claim.booking_reference_code}
                                        </span>
                                    </div>
                                )}

                                {claim.booked_at && (
                                    <div>
                                        <span className="text-xs text-slate-500 block">
                                            Booked Date
                                        </span>
                                        <span className="text-slate-200 font-medium">
                                            {formatDate(claim.booked_at)}
                                        </span>
                                    </div>
                                )}

                                {claim.payment_method && (
                                    <div>
                                        <span className="text-xs text-slate-500 block">
                                            Payment Method
                                        </span>
                                        <span className="text-slate-200 font-medium">
                                            {claim.payment_method}
                                        </span>
                                    </div>
                                )}

                                {claim.posted_at && (
                                    <div>
                                        <span className="text-xs text-slate-500 block">
                                            Posted Date
                                        </span>
                                        <span className="text-slate-200 font-medium">
                                            {formatDate(claim.posted_at)}
                                        </span>
                                    </div>
                                )}
                            </div>
                        </div>
                    )}

                {/* Line Items & History Section */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div className="lg:col-span-2 bg-[#111827] border border-slate-800 rounded-xl overflow-hidden shadow-xl">
                        <div className="px-6 py-4 border-b border-slate-800 bg-[#161e2e]">
                            <h2 className="text-sm font-semibold text-white uppercase tracking-wider">
                                Line Items
                            </h2>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-slate-800 text-left text-sm">
                                <thead className="bg-[#161e2e] text-slate-400 font-medium">
                                    <tr>
                                        <th className="px-6 py-3">Category</th>
                                        <th className="px-6 py-3">
                                            Description
                                        </th>
                                        <th className="px-6 py-3 text-right">
                                            VAT
                                        </th>
                                        <th className="px-6 py-3 text-right">
                                            Amount
                                        </th>
                                        <th className="px-6 py-3 text-right">
                                            Receipt
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {claim?.items_count > 0 ? (
                                        claim?.items?.map((item) => {
                                            const itemCurrency =
                                                item.currency.code || "AED";
                                            const isForeignCurrency =
                                                item.currency.code !==
                                                claim.currency.code;

                                            return (
                                                <tr
                                                    key={item.id}
                                                    className="hover:bg-slate-800/40 transition"
                                                >
                                                    <td className="px-6 py-4 font-medium text-white">
                                                        {item.category?.name ||
                                                            "General"}
                                                    </td>
                                                    <td className="px-6 py-4 text-slate-400">
                                                        {item.description}
                                                    </td>
                                                    <td className="px-6 py-4 text-right font-semibold text-white">
                                                        {itemCurrency}{" "}
                                                        {Number(
                                                            item.vat_amount ||
                                                                0,
                                                        ).toFixed(2)}
                                                    </td>
                                                    <td className="px-6 py-4 text-right font-semibold text-white">
                                                        <div>
                                                            {itemCurrency}{" "}
                                                            {Number(
                                                                item.amount ||
                                                                    0,
                                                            ).toFixed(2)}
                                                        </div>
                                                        {isForeignCurrency &&
                                                            item.amount_local_currency && (
                                                                <div className="text-xs text-slate-500 font-normal">
                                                                    ({" "}
                                                                    {
                                                                        claim
                                                                            .currency
                                                                            .code
                                                                    }{" "}
                                                                    {Number(
                                                                        item.amount_local_currency ||
                                                                            0,
                                                                    ).toFixed(
                                                                        2,
                                                                    )}
                                                                    )
                                                                </div>
                                                            )}
                                                    </td>
                                                    <td className="px-6 py-4 text-right">
                                                        {item.attachment_path ? (
                                                            <a
                                                                href={route(
                                                                    "expense-claim.items.attachment",
                                                                    item.id,
                                                                )}
                                                                target="_blank"
                                                                rel="noopener noreferrer"
                                                                className="inline-flex items-center gap-1.5 text-amber-400 hover:text-amber-300 transition-colors underline font-medium text-xs"
                                                            >
                                                                <span>
                                                                    {item.attachment_ref ||
                                                                        "View Receipt"}
                                                                </span>
                                                            </a>
                                                        ) : (
                                                            <span className="text-slate-500 text-xs italic">
                                                                No attachment
                                                            </span>
                                                        )}
                                                    </td>
                                                </tr>
                                            );
                                        })
                                    ) : (
                                        <tr>
                                            <td
                                                colSpan="10"
                                                className="text-center"
                                            >
                                                No items found
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div className="bg-[#111827] border border-slate-800 rounded-xl p-6 space-y-4 shadow-xl">
                        <h2 className="text-sm font-semibold text-white uppercase tracking-wider border-b border-slate-800 pb-3">
                            Workflow History
                        </h2>
                        <div className="space-y-3">
                            {!claim?.histories ||
                            claim?.histories.length === 0 ? (
                                <p className="text-xs text-slate-500">
                                    No activity recorded yet.
                                </p>
                            ) : (
                                claim.histories.map((history) => {
                                    return (
                                        <div
                                            key={history.id}
                                            className="border-l-2 border-slate-700 pl-4 py-1 space-y-1"
                                        >
                                            <span className="font-semibold text-white">
                                                {history.action_by?.name ||
                                                    "System"}{" "}
                                            </span>
                                            <span className="text-slate-500">
                                                {history?.created_at
                                                    ? new Date(
                                                          history.created_at,
                                                      ).toLocaleDateString(
                                                          "en-GB",
                                                          {
                                                              day: "2-digit",
                                                              month: "short",
                                                              year: "numeric",
                                                          },
                                                      )
                                                    : "-"}
                                            </span>
                                            <p className="text-xs font-medium text-amber-400 capitalize">
                                                {history.action}
                                            </p>
                                            {history.comments && (
                                                <p className="text-xs text-slate-400 bg-slate-800/60 p-2 rounded border border-slate-800">
                                                    "{history.comments}"
                                                </p>
                                            )}
                                        </div>
                                    );
                                })
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
