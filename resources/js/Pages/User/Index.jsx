import React, { useState, useEffect } from "react";
import { Head, Link, router, usePage } from "@inertiajs/react";
import { Eye, Edit, KeyRound, Ban } from "lucide-react";
import AuthenticatedLayout from "../../Components/AuthenticatedLayout";
import { route } from "ziggy-js";

export default function Index({ auth, users, filters }) {
    const { flash } = usePage().props;

    const userData = users.data || "";

    const [search, setSearch] = useState(filters.search || "");

    // Debounce search input to avoid hitting backend on every keystroke
    useEffect(() => {
        const timer = setTimeout(() => {
            if (search !== (filters.search || "")) {
                router.get(
                    route("users.index"),
                    { search: search },
                    { preserveState: true, replace: true },
                );
            }
        }, 400);

        return () => clearTimeout(timer);
    }, [search]);

    const [processingUserId, setProcessingUserId] = useState(null);

    const handleResendLink = (userid) => {
        if (
            confirm(
                "Are you sure you want to resend the activation/password link to this user?",
            )
        ) {
            router.post(
                route("user.resend-link", userid),
                {},
                {
                    preserveScroll: true,
                    onStart: () => setProcessingUserId(userid),
                    onFinish: () => setProcessingUserId(null),
                },
            );
        }
    };

    return (
        <AuthenticatedLayout auth={auth}>
            <Head title="User Directory" />
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
                                {users.from || 1}
                            </span>{" "}
                            to{" "}
                            <span className="font-semibold text-white">
                                {users.to || users.data.length}
                            </span>{" "}
                            of{" "}
                            <span className="font-semibold text-white">
                                {users.total || users.data.length}
                            </span>{" "}
                            users.
                        </p>
                        <Link
                            href={route("user.create")}
                            className="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-500 active:bg-blue-700 rounded-lg shadow-md hover:shadow-blue-500/20 transition-all duration-150"
                        >
                            <span className="text-base leading-none">+</span>
                            <span>Onboard New User</span>
                        </Link>
                    </div>
                    {/* DIRECTORY MATRIX CARD */}
                    <div className="bg-slate-900/60 backdrop-blur-md border border-slate-800 rounded-xl overflow-hidden shadow-xl">
                        <div className="overflow-x-auto">
                            <table className="w-full text-left border-collapse">
                                <thead>
                                    <tr className="border-b border-gray-800 bg-[#242427]/50 text-gray-400 text-xs uppercase tracking-wider font-semibold">
                                        <th className="px-6 py-4">User Name</th>
                                        <th className="px-6 py-4">
                                            Primary Corporate Matrix
                                        </th>
                                        <th className="px-6 py-4">Status</th>
                                        <th className="px-4 py-3 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-800/60 text-sm">
                                    {userData.length === 0 ? (
                                        <tr>
                                            <td
                                                colSpan="5"
                                                className="px-6 py-12 text-center text-gray-500"
                                            >
                                                No corporate user records found.
                                                Click "Onboard New User" to
                                                register profiles.
                                            </td>
                                        </tr>
                                    ) : (
                                        userData.map((usr) => (
                                            <tr
                                                key={usr.id}
                                                className="hover:bg-gray-800/20 transition-colors group"
                                            >
                                                {/* Profile Details */}
                                                <td className="px-6 py-4">
                                                    <div className="font-medium text-white">
                                                        {usr.name}
                                                    </div>
                                                    <div className="text-xs text-gray-500 mt-0.5">
                                                        {usr.email}
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4">
                                                    <div className="font-medium text-white">
                                                        {usr.employee
                                                            ?.designation
                                                            ?.name ||
                                                            "No Designation"}
                                                    </div>
                                                    <div className="text-xs text-gray-500 mt-0.5">
                                                        {usr.employee.company
                                                            ?.name ||
                                                            "Unassigned"}{" "}
                                                        |{" "}
                                                        {usr.employee.department
                                                            ?.name ||
                                                            "Unassigned"}
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 font-medium text-white">
                                                    {usr.is_active ? (
                                                        <span className="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                            Active
                                                        </span>
                                                    ) : (
                                                        <span className="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                                            Inactive
                                                        </span>
                                                    )}
                                                </td>
                                                <td className="px-4 py-3 text-center whitespace-nowrap">
                                                    {usr.is_admin === 0 && (
                                                        <div className="flex items-center justify-center gap-1.5 opacity-70 group-hover:opacity-100 transition-opacity">
                                                            <Link
                                                                href={route(
                                                                    "user.show",
                                                                    usr.id,
                                                                )}
                                                                title="View User Access"
                                                                className="p-1.5 rounded-md text-slate-400 hover:text-blue-400 hover:bg-blue-500/10 transition-all"
                                                            >
                                                                <Eye className="w-4 h-4" />
                                                            </Link>

                                                            <Link
                                                                href={route(
                                                                    "user.edit",
                                                                    usr.id,
                                                                )}
                                                                title="Edit User Access"
                                                                className="p-1.5 rounded-md text-slate-400 hover:text-amber-400 hover:bg-amber-500/10 transition-colors"
                                                            >
                                                                <Edit className="w-4 h-4" />
                                                            </Link>
                                                            <button
                                                                type="button"
                                                                title="Resend Password Setup Link"
                                                                disabled={
                                                                    processingUserId ===
                                                                    usr.id
                                                                }
                                                                onClick={() =>
                                                                    handleResendLink(
                                                                        usr.id,
                                                                    )
                                                                }
                                                                className={`p-1.5 rounded-md text-slate-400 hover:text-cyan-400 hover:bg-cyan-500/10 transition-all ${
                                                                    processingUserId ===
                                                                    usr.id
                                                                        ? "opacity-50 animate-pulse cursor-not-allowed"
                                                                        : ""
                                                                }`}
                                                                className="p-1.5 rounded-md text-slate-400 hover:text-cyan-400 hover:bg-cyan-500/10 transition-all"
                                                            >
                                                                <KeyRound className="w-4 h-4" />
                                                            </button>
                                                            <button
                                                                type="button"
                                                                title="Deactivate User"
                                                                onClick={() => {
                                                                    if (
                                                                        confirm(
                                                                            `Are you sure you want to deactivate ${usr.name}'s profile?`,
                                                                        )
                                                                    ) {
                                                                        router.delete(
                                                                            route(
                                                                                "user.delete",
                                                                                usr.id,
                                                                            ),
                                                                        );
                                                                    }
                                                                }}
                                                                className="p-1.5 rounded-md text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition-colors text-xs font-medium"
                                                            >
                                                                <Ban className="w-4 h-4" />
                                                            </button>
                                                        </div>
                                                    )}
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
