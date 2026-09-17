import React from "react";
import { Head, Link, router, usePage } from "@inertiajs/react";
import AuthenticatedLayout from "../../Components/AuthenticatedLayout";
import StatusBadge from "../../Components/StatusBadge";

export default function Index({ auth, claims, filters = {} }) {
    const { flash } = usePage().props;

    const claimdata = claims.data || "";

    return (
        <AuthenticatedLayout auth={auth}>
            <Head title="Expense claims Directory" />
            <div className="py-12 bg-lumina-darkBg min-h-screen text-gray-200">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    {flash?.success && (
                        <div className="bg-emerald-950/40 border border-emerald-800 rounded-lg px-4 py-3 shadow-sm mb-6">
                            <span
                                style={{
                                    color: "#278a17",
                                    fontWeight: "600",
                                    fontSize: "14px",
                                    display: "block",
                                }}
                            >
                                {flash.success}
                            </span>
                        </div>
                    )}
                    <div className="flex items-center justify-between">
                        <p className="text-sm text-slate-400">
                            Showing{" "}
                            <span className="font-semibold text-white">
                                {claims.from || 1}
                            </span>{" "}
                            to{" "}
                            <span className="font-semibold text-white">
                                {claims.to || claims.data.length}
                            </span>{" "}
                            of{" "}
                            <span className="font-semibold text-white">
                                {claims.total || claims.data.length}
                            </span>{" "}
                            claims.
                        </p>

                        <Link
                            href={route("expense-claim.create")}
                            className="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-500 active:bg-blue-700 rounded-lg shadow-md hover:shadow-blue-500/20 transition-all duration-150"
                        >
                            <span className="text-base leading-none">+</span>
                            <span>Submit New Claim</span>
                        </Link>
                    </div>

                    <div className="bg-slate-900/60 border border-slate-800 rounded-xl shadow-xl overflow-hidden">
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-sm text-slate-300 table-fixed">
                                <thead className="bg-slate-950/80 text-slate-400 uppercase text-xs tracking-wider border-b border-slate-800">
                                    <tr>
                                        <th className="px-6 py-4 font-medium">
                                            Claim Ref
                                        </th>
                                        <th className="px-6 py-4 font-medium">
                                            Company
                                        </th>

                                        <th className="px-6 py-4 font-medium">
                                            Employee
                                        </th>
                                        <th className="px-6 py-4 font-medium">
                                            Date
                                        </th>
                                        <th className="px-6 py-4 font-medium">
                                            Total Amount
                                        </th>
                                        <th className="px-6 py-4 font-medium">
                                            Status
                                        </th>
                                        <th className="px-6 py-4 font-medium text-right">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-800/60">
                                    {claimdata.length === 0 ? (
                                        <tr>
                                            <td
                                                colSpan="5"
                                                className="px-6 py-12 text-center text-gray-500"
                                            >
                                                No claims submitted. Click
                                                "Submit New Claim" to submit
                                                your claim.
                                            </td>
                                        </tr>
                                    ) : (
                                        claimdata.map((claim) => (
                                            <tr
                                                key={claim.id}
                                                className="hover:bg-slate-800/40 transition-colors"
                                            >
                                                <td className="px-6 py-4 font-semibold text-blue-400 whitespace-nowrap">
                                                    <Link
                                                        href={route(
                                                            "expense-claim.show",
                                                            claim.id,
                                                        )}
                                                        className="hover:underline"
                                                    >
                                                        {claim.claim_number}
                                                    </Link>
                                                </td>
                                                <td className="px-6 py-4 text-slate-300 whitespace-nowrap">
                                                    {claim.company.name ||
                                                        "N/A"}
                                                </td>
                                                <td className="px-6 py-4 text-slate-300 whitespace-nowrap">
                                                    {claim.employee.name ||
                                                        "N/A"}
                                                </td>
                                                <td className="px-6 py-4 text-slate-400 whitespace-nowrap">
                                                    {new Date(
                                                        claim.created_at,
                                                    ).toLocaleDateString(
                                                        "en-GB",
                                                        {
                                                            day: "2-digit",
                                                            month: "short",
                                                            year: "numeric",
                                                        },
                                                    )}
                                                </td>
                                                <td className="px-6 py-4 font-medium text-white whitespace-nowrap">
                                                    {claim.currency?.code ||
                                                        "AED"}{" "}
                                                    {Number(
                                                        claim.total_amount_local_currency ||
                                                            0,
                                                    ).toLocaleString(
                                                        undefined,
                                                        {
                                                            minimumFractionDigits: 2,
                                                            maximumFractionDigits: 2,
                                                        },
                                                    )}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <StatusBadge
                                                        status={claim.status}
                                                    />
                                                </td>
                                                <td className="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                                    <Link
                                                        href={route(
                                                            "expense-claim.show",
                                                            claim.id,
                                                        )}
                                                        className="text-xs font-medium px-2.5 py-1.5 rounded bg-slate-800 text-slate-300 hover:bg-slate-700 transition-colors"
                                                    >
                                                        View
                                                    </Link>
                                                    {/*

                                                    {claim.can.update &&
                                                        [
                                                            "draft",
                                                            "pending_verification",
                                                            "rejected",
                                                        ].includes(
                                                            claim.status,
                                                        ) && (
                                                            <Link
                                                                href={route(
                                                                    "expense-claim.edit",
                                                                    claim.id,
                                                                )}
                                                                className="text-xs font-medium px-2.5 py-1.5 rounded bg-blue-950/80 text-blue-400 hover:bg-blue-900/80 transition-colors"
                                                            >
                                                                Edit
                                                            </Link>
                                                        )} */}
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
