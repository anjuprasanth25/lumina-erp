import React from "react";
import { Head, Link } from "@inertiajs/react";
import AuthenticatedLayout from "../../Components/AuthenticatedLayout";
import { route } from "ziggy-js";
import { ArrowLeft } from "lucide-react";

export default function Show({ auth, user, companyAccessBlocks }) {
    return (
        <AuthenticatedLayout auth={auth}>
            <div className="min-h-screen bg-lumina-darkBg p-8 text-white">
                <Head title={`User Profile - ${user.name}`} />
                <div className="max-w-4xl mx-auto">
                    {/* HEADER LOG */}
                    <div className="flex justify-between items-center mb-6">
                        <h2 className="text-xl font-semibold tracking-wide text-white">
                            User Profile:{" "}
                            <span className="text-amber-500">{user.name}</span>
                        </h2>
                        <Link
                            href={route("user.index")}
                            className="inline-flex items-center gap-1.5 text-sm font-medium text-slate-400 hover:text-white transition-colors"
                        >
                            <ArrowLeft className="w-4 h-4" />
                            <span>Back to Directory</span>
                        </Link>
                    </div>
                </div>
                <div className="bg-slate-900/60 backdrop-blur-md border border-slate-800 rounded-xl p-6 shadow-xl">
                    <h3 className="mb-2 font-bold text-white">
                        Employee Information
                    </h3>
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <span className="block text-xs text-slate-500">
                                Employee Name
                            </span>
                            <span className="text-sm font-semibold text-white">
                                {user.name}
                            </span>
                        </div>

                        <div>
                            <span className="block text-xs text-slate-500">
                                Email
                            </span>
                            <span className="text-sm text-slate-300">
                                {user.email}
                            </span>
                        </div>

                        <div>
                            <span className="block text-xs text-slate-500">
                                Primary Company
                            </span>
                            <span className="text-sm text-slate-300">
                                {user?.employee?.company?.name || ""}
                            </span>
                        </div>

                        <div>
                            <span className="block text-xs text-slate-500">
                                Designation
                            </span>
                            <span
                                className="text-sm text-slate-300 block truncate"
                                title={`${user?.employee?.designation?.name || ""} - ${user?.employee?.department?.name || ""}`}
                            >
                                {user?.employee?.designation?.name}-
                                {user?.employee?.department?.name}
                            </span>
                        </div>
                    </div>
                </div>

                <div className="space-y-6">
                    {companyAccessBlocks.map((block) => (
                        <div
                            key={block.company_id}
                            className="bg-[#101726] border border-slate-800/80 rounded-xl p-5 shadow-lg space-y-4"
                        >
                            {/* Company Header */}
                            <div className="flex items-center justify-between pb-3 border-b border-slate-800/80">
                                <div className="flex items-center gap-2">
                                    <span className="text-xs uppercase font-bold tracking-wider text-amber-500 bg-amber-500/10 px-2.5 py-1 rounded-md">
                                        Company
                                    </span>
                                    <h3 className="text-base font-medium text-white">
                                        {block.company_name}
                                    </h3>
                                </div>
                                {block.is_primary && (
                                    <span className="text-xs font-medium text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-0.5 rounded-full">
                                        Primary Company
                                    </span>
                                )}
                            </div>

                            {/* Modules & Roles List */}
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                                {block.permissions.map((perm) => (
                                    <div
                                        key={perm.id}
                                        className="flex items-center justify-between p-3 rounded-lg bg-[#162032] border border-slate-800/50"
                                    >
                                        <span className="text-sm font-medium text-slate-200">
                                            {perm.module_name}
                                        </span>
                                        <span
                                            className={`text-xs font-semibold px-2.5 py-1 rounded-md border ${
                                                perm.role_name ===
                                                "Administrator"
                                                    ? "bg-amber-500/10 text-amber-400 border-amber-500/30"
                                                    : "bg-slate-700/40 text-slate-300 border-slate-700/60"
                                            }`}
                                        >
                                            {perm.role_name}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
