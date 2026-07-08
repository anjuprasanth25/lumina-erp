import React from "react";
import { Head, Link, router, usePage } from "@inertiajs/react";
import AuthenticatedLayout from "../../Components/AuthenticatedLayout";
import { route } from "ziggy-js";

export default function Index({ auth, employees }) {
    const { flash } = usePage().props;

    const employeeData = employees.data || [];

    return (
        <AuthenticatedLayout auth={auth}>
            <Head title="Employee Directory" />
            <div className="py-12 bg-[#121214] min-h-screen text-gray-200">
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
                        <p className="text-sm text-gray-400">
                            Showing
                            <span className="text-white font-medium p-2">
                                {employees.from || 0}
                            </span>
                            to{" "}
                            <span className="text-white font-medium p-2">
                                {employees.to || 0}
                            </span>
                            of{" "}
                            <span className="text-white font-medium p-2">
                                {employees.total || 0}
                            </span>
                            workspaces.
                        </p>
                        <Link href={route("employee.create")}>
                            <span className="text-white font-thin">
                                + Onboard New Employee
                            </span>
                        </Link>
                    </div>
                    {/* DIRECTORY MATRIX CARD */}
                    <div className="bg-[#1c1c1e] border border-gray-800 rounded-xl overflow-hidden shadow-xl">
                        <div className="overflow-x-auto">
                            <table className="w-full text-left border-collapse">
                                <thead>
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
                                        <th className="px-6 py-4 text-center">
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
                                                    <div className="font-medium text-gray-500 mt-0.5">
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
                                                <td className="px-6 py-4 text-right">
                                                    {emp.is_system_record ===
                                                        0 && (
                                                        <div className="flex justify-end gap-3 opacity-60 group-hover:opacity-100 transition-opacity">
                                                            <Link
                                                                className="text-gray-400 hover:text-amber-500 transition-colors"
                                                                href={route(
                                                                    "employee.show",
                                                                    emp.id,
                                                                )}
                                                            >
                                                                View
                                                            </Link>

                                                            <Link
                                                                className="text-gray-400 hover:text-amber-500 transition-colors"
                                                                href={route(
                                                                    "employee.edit",
                                                                    emp.id,
                                                                )}
                                                            >
                                                                Edit
                                                            </Link>
                                                            <button
                                                                className="text-gray-400 hover:text-amber-500 transition-colors text-xs font-medium"
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
                                                                Deactivate
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
