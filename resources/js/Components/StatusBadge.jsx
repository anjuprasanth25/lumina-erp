import React from "react";

// Make sure "export default" is present here
export default function StatusBadge({ status, className = "" }) {
    const styles = {
        draft: "bg-slate-800 text-slate-300 border-slate-700",
        pending_verification:
            "bg-amber-950/60 text-amber-400 border-amber-800/60",
        pending_approval: "bg-blue-950/60 text-blue-400 border-blue-800/60",
        pending_finance_approval:
            "bg-purple-950/60 text-purple-300 border-purple-800/60",
        approved: "bg-emerald-950/60 text-emerald-400 border-emerald-800/60",
        rejected: "bg-rose-950/60 text-rose-400 border-rose-800/60",
        booked: "bg-cyan-950/60 text-cyan-400 border-cyan-800/60",
        posted: "bg-teal-950/60 text-teal-300 border-teal-800/60",
    };

    const labels = {
        draft: "Draft",
        pending_verification: "Pending Verification",
        pending_approval: "Pending Approval",
        pending_finance_approval: "Pending Finance Approval",
        approved: "Approved",
        rejected: "Rejected",
        booked: "Booked",
        posted: "Posted",
    };

    return (
        <span
            className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border whitespace-nowrap ${
                styles[status] || styles.draft
            } ${className}`}
        >
            {labels[status] || status}
        </span>
    );
}
