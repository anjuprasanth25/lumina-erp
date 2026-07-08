import React from "react";
import { Head, Link } from "@inertiajs/react";
import AuthenticatedLayout from "../../Components/AuthenticatedLayout";
import TextInput from "../../Components/TextInput";
import SelectInput from "../../Components/SelectInput";
import { route } from "ziggy-js";

export default function Edit({ auth, employee }) {
    return (
        <AuthenticatedLayout auth={auth}>
            <div className="min-h-screen bg-[#0b0f19] p-8 text-white">
                <Head title={`Employee Profile - ${employee.name}`} />

                <div className="max-w-4xl mx-auto space-y-6">
                    {/* HEADER LOG */}
                    <div className="flex justify-between items-center mb-6">
                        <h2 className="text-xl font-semibold tracking-wide text-white">
                            Employee Profile:{" "}
                            <span className="text-amber-500">
                                {employee.name}
                            </span>
                        </h2>
                        <Link
                            href={route("employee.index")}
                            className="text-sm text-gray-400 hover:text-white transition-colors"
                        >
                            Back to Directory
                        </Link>
                    </div>
                    {/* CARD CONTAINER */}
                    <div className="bg-[#141416] border border-gray-800 rounded-xl p-6 shadow-xl">
                        <h3 className="mb-2 font-bold">Personal Information</h3>
                        {/* 🌟 FORCE 2 COLUMNS AND PREVENT FLEX BREAKING BY WRAPPING TARGETS */}
                        <div
                            style={{
                                display: "grid",
                                gridTemplateColumns: "1fr 1fr",
                                gap: "24px",
                            }}
                        >
                            <TextInput
                                label="Employee Code"
                                type="text"
                                value={employee.code}
                                disabled
                            />
                            <TextInput
                                label="Email"
                                type="text"
                                value={employee.email}
                                disabled
                            />

                            <TextInput
                                label="First Name"
                                type="text"
                                value={employee.first_name}
                                disabled
                            />

                            <TextInput
                                label="Middle Name"
                                type="text"
                                value={employee.middle_name}
                                disabled
                            />

                            <TextInput
                                label="Last Name"
                                type="text"
                                value={employee.last_name}
                                disabled
                            />

                            <TextInput
                                label="DOB"
                                type="date"
                                value={employee.dob}
                                disabled
                            />

                            <TextInput
                                label="Gender"
                                type="text"
                                value={
                                    employee.details.gender == "F"
                                        ? "Female"
                                        : "Male"
                                }
                                disabled
                            />

                            <TextInput
                                label="Family Status"
                                type="text"
                                value={
                                    employee.details.family_status
                                        ? employee.details.family_status
                                              .charAt(0)
                                              .toUpperCase() +
                                          employee.details.family_status.slice(
                                              1,
                                          )
                                        : ""
                                }
                                disabled
                            />
                        </div>
                        {/* END OF GRID */}
                    </div>
                    {/* END OF CARD */}

                    <div className="bg-[#141416] border border-gray-800 rounded-xl p-6 shadow-xl pt-10">
                        <h3 className="mb-2 font-bold">
                            Employement Information
                        </h3>
                        <div
                            style={{
                                display: "grid",
                                gridTemplateColumns: "1fr 1fr",
                                gap: "24px",
                            }}
                        >
                            <TextInput
                                label="Joining Date"
                                type="text"
                                value={employee.date_of_joining}
                                disabled
                            />

                            <TextInput
                                label="Company"
                                type="text"
                                value={employee.company?.name}
                                disabled
                            />

                            <TextInput
                                label="Department"
                                type="text"
                                value={employee.department?.name}
                                disabled
                            />

                            <TextInput
                                label="Designation"
                                type="text"
                                value={employee.designation?.name}
                                disabled
                            />

                            <TextInput
                                label="Line Manager"
                                type="text"
                                value={employee.line_manager?.name}
                                disabled
                            />

                            <TextInput
                                label="Country"
                                type="text"
                                value={employee.country?.name}
                                disabled
                            />

                            <TextInput
                                label="Billing Type"
                                type="text"
                                value={employee.billing_type?.name}
                                disabled
                            />
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
