import React from "react";
import { Head, Link, router, usePage } from "@inertiajs/react";
import AuthenticatedLayout from "../../Components/AuthenticatedLayout";
import { Eye, Edit, KeyRound, Ban } from "lucide-react";
import { route } from "ziggy-js";

export default function Index({ auth, employees }) {
    const { flash } = usePage().props;

    const employeeData = employees.data || [];

    return (
        <AuthenticatedLayout auth={auth}>
            <Head title="Employee Directory" />
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
                                {employees.from || 1}
                            </span>{" "}
                            to{" "}
                            <span className="font-semibold text-white">
                                {employees.to || employees.data.length}
                            </span>{" "}
                            of{" "}
                            <span className="font-semibold text-white">
                                {employees.total || employees.data.length}
                            </span>{" "}
                            employees.
                        </p>
                        <Link
                            href={route("employee.create")}
                            className="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-500 active:bg-blue-700 rounded-lg shadow-md hover:shadow-blue-500/20 transition-all duration-150"
                        >
                            <span className="text-base leading-none">+</span>
                            <span>Onboard New Employee</span>
                        </Link>
                    </div>
                    {/* DIRECTORY MATRIX CARD */}
                    <div className="bg-slate-900/60 backdrop-blur-md border border-slate-800 rounded-xl overflow-hidden shadow-xl">
                        <div className="overflow-x-auto">
                            <table
                                className="w-full text-left border-collapse"
                                table-fixed
                            >
                                <thead className="bg-[#1c273e] text-xs uppercase text-slate-400 font-semibold border-b border-slate-800">
                                    <tr className="border-b border-gray-800 bg-[#242427]/50 text-gray-400 text-xs uppercase tracking-wider font-semibold">
                                        <th className="px-6 py-4">
                                            Employee Code
                                        </th>
                                        <th className="px-6 py-4">
                                            Employee Name
                                        </th>
                                        <th className="px-6 py-4">
                                            Corporate Matrix
                                        </th>
                                        <th className="px-6 py-4">
                                            Joining Date
                                        </th>
                                        <th className="px-6 py-4">Status</th>
                                        <th className="px-4 py-3 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-800/60 text-sm">
                                    {employeeData.length === 0 ? (
                                        <tr>
                                            <td
                                                colSpan="5"
                                                className="px-6 py-12 text-center text-gray-500"
                                            >
                                                No corporate employee records
                                                found. Click "Onboard New
                                                Employee" to register profiles.
                                            </td>
                                        </tr>
                                    ) : (
                                        employeeData.map((emp) => (
                                            <tr
                                                key={emp.id}
                                                className="hover:bg-gray-800/20 transition-colors group"
                                            >
                                                <td className="px-6 py-4 text-white">
                                                    {emp.code}
                                                </td>
                                                {/* Profile Details */}
                                                <td className="px-6 py-4">
                                                    <div className="font-medium text-white">
                                                        {emp.name}
                                                    </div>
                                                    <div className="text-xs text-gray-500 mt-0.5">
                                                        {emp.email}
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4">
                                                    <div className="font-medium text-gray-300 text-white">
                                                        {emp.designation
                                                            ?.name ||
                                                            "No Designation"}
                                                    </div>
                                                    <div className="text-xs text-gray-500 mt-0.5">
                                                        {emp.company?.name ||
                                                            "Unassigned"}{" "}
                                                        |{" "}
                                                        {emp.department?.name ||
                                                            "Unassigned"}
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 font-medium text-white">
                                                    {emp.date_of_joining
                                                        ? new Date(
                                                              emp.date_of_joining,
                                                          ).toLocaleDateString(
                                                              "en-US",
                                                              {
                                                                  year: "numeric",
                                                                  month: "short",
                                                                  day: "numeric",
                                                              },
                                                          )
                                                        : "-"}
                                                </td>
                                                <td className="px-6 py-4 font-medium text-white">
                                                    {emp.is_active ? (
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
                                                    {emp.is_system_record ===
                                                        0 && (
                                                        <div className="flex items-center justify-center gap-1.5 opacity-70 group-hover:opacity-100 transition-opacity">
                                                            <Link
                                                                title="View Employee"
                                                                className="p-1.5 rounded-md text-slate-400 hover:text-blue-400 hover:bg-blue-500/10 transition-all"
                                                                href={route(
                                                                    "employee.show",
                                                                    emp.id,
                                                                )}
                                                            >
                                                                <Eye className="w-4 h-4" />
                                                            </Link>

                                                            <Link
                                                                title="Edit Employee"
                                                                className="p-1.5 rounded-md text-slate-400 hover:text-amber-400 hover:bg-amber-500/10 transition-colors"
                                                                href={route(
                                                                    "employee.edit",
                                                                    emp.id,
                                                                )}
                                                            >
                                                                <Edit className="w-4 h-4" />
                                                            </Link>
                                                            <button
                                                                title="Deactivate Employee"
                                                                className="p-1.5 rounded-md text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition-colors text-xs font-medium"
                                                                onClick={() => {
                                                                    if (
                                                                        confirm(
                                                                            `Are you sure you want to deactivate ${emp.name}'s profile?`,
                                                                        )
                                                                    ) {
                                                                        router.delete(
                                                                            route(
                                                                                "employee.delete",
                                                                                emp.id,
                                                                            ),
                                                                        );
                                                                    }
                                                                }}
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
