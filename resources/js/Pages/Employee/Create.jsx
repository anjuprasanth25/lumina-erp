import React from "react";
import { useForm, Head, Link } from "@inertiajs/react";
import AuthenticatedLayout from "../../Components/AuthenticatedLayout";
import TextInput from "../../Components/TextInput";
import SelectInput from "../../Components/SelectInput";
import { route } from "ziggy-js";

export default function EmployeeOnboarding({ auth, lookups = {} }) {
    const {
        companies = [],
        departments = [],
        designation = [],
        lineManager = [],
        countries = [],
        billingtypes = [],
    } = lookups;

    // Initialize Inertia form hook mapping to your tables
    const { data, setData, post, processing, errors } = useForm({
        code: "",
        email: "",
        first_name: "",
        middle_name: "",
        last_name: "",
        dob: "",
        gender: "",
        family_status: "",
        date_of_joining: "",
        company_id: "",
        department_id: "",
        designation_id: "",
        country_id: "",
        billing_type_id: "",
        line_manager_id: "",
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route("employee.store"), {
            preserveScroll: true, // Prevents sudden window jumping if errors return
            onSuccess: () => {
                // Optional: Add a flash notification or tracking logic here
            },
        });
    };

    return (
        <AuthenticatedLayout auth={auth}>
            <Head title="Employee Onboarding" />

            <div className="max-w-5xl mx-auto p-6 text-gray-200">
                <h1 className="text-2xl font-bold text-white mb-6">
                    Employee Onboarding
                </h1>
                {errors.error && (
                    <div className="mb-6 p-4 bg-red-950/40 border border-red-500/50 rounded-lg flex items-start gap-3">
                        <span className="text-red-500 font-bold text-lg leading-none">
                            ⚠️
                        </span>
                        <div>
                            <h4 className="text-red-400 font-semibold text-sm">
                                System Error
                            </h4>
                            <p className="text-gray-300 text-xs mt-1 leading-relaxed">
                                {errors.error}
                            </p>
                        </div>
                    </div>
                )}
                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* CARD CONTAINER */}
                    <div className="bg-[#141416] border border-gray-800 rounded-xl p-6 shadow-xl">
                        <h3 className="mb-2 font-bold text-white">
                            Personal Information
                        </h3>
                        {/* 🌟 FORCE 2 COLUMNS AND PREVENT FLEX BREAKING BY WRAPPING TARGETS */}
                        <div
                            style={{
                                display: "grid",
                                gridTemplateColumns: "1fr 1fr",
                                gap: "24px",
                            }}
                        >
                            <TextInput
                                label="Employee Code *"
                                type="text"
                                value={data.code}
                                onChange={(e) =>
                                    setData("code", e.target.value)
                                }
                                error={errors.code}
                                required
                            />
                            <TextInput
                                label="Email *"
                                type="text"
                                value={data.email}
                                onChange={(e) =>
                                    setData("email", e.target.value)
                                }
                                error={errors.email}
                                required
                            />

                            <TextInput
                                label="First Name *"
                                type="text"
                                value={data.first_name}
                                onChange={(e) =>
                                    setData("first_name", e.target.value)
                                }
                                error={errors.first_name}
                                required
                            />

                            <TextInput
                                label="Middle Name "
                                type="text"
                                value={data.middle_name}
                                onChange={(e) =>
                                    setData("middle_name", e.target.value)
                                }
                                error={errors.middle_name}
                            />

                            <TextInput
                                label="Last Name *"
                                type="text"
                                value={data.last_name}
                                onChange={(e) =>
                                    setData("last_name", e.target.value)
                                }
                                error={errors.last_name}
                                required
                            />
                            {/*
                        <TextInput
                            label="Full Name"
                            type="text"
                            value={`${data.first_name ||''} ${data.last_name || ''}`.trim()}
                            readOnly
                        /> */}

                            <TextInput
                                label="DOB *"
                                type="date"
                                value={data.dob}
                                error={errors.dob}
                                onChange={(e) => setData("dob", e.target.value)}
                            />

                            <SelectInput
                                label="Gender *"
                                value={data.gender}
                                onChange={(e) =>
                                    setData("gender", e.target.value)
                                }
                                options={[
                                    { id: "M", name: "Male" },
                                    { id: "F", name: "Female" },
                                ]}
                                error={errors.gender}
                            />

                            <SelectInput
                                label="Family Status *"
                                value={data.family_status}
                                onChange={(e) =>
                                    setData("family_status", e.target.value)
                                }
                                options={[
                                    { id: "single", name: "Single" },
                                    { id: "married", name: "Married" },
                                ]}
                                error={errors.family_status}
                            />
                        </div>{" "}
                        {/* END OF GRID */}
                    </div>{" "}
                    {/* END OF CARD */}
                    <div className="bg-[#141416] border border-gray-800 rounded-xl p-6 shadow-xl">
                        <h3 className="mb-2 font-bold text-white">
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
                                label="Joining Date *"
                                type="date"
                                value={data.date_of_joining}
                                onChange={(e) =>
                                    setData("date_of_joining", e.target.value)
                                }
                                error={errors.date_of_joining}
                            />

                            <SelectInput
                                id="compid"
                                label="Company"
                                value={data.company_id}
                                onChange={(e) =>
                                    setData("company_id", e.target.value)
                                }
                                options={companies}
                                error={errors.company_id}
                            />

                            <SelectInput
                                label="Department"
                                value={data.department_id}
                                onChange={(e) =>
                                    setData("department_id", e.target.value)
                                }
                                options={departments}
                                error={errors.department_id}
                            />

                            <SelectInput
                                label="Designation *"
                                value={data.designation_id}
                                onChange={(e) =>
                                    setData("designation_id", e.target.value)
                                }
                                options={designation}
                                error={errors.designation_id}
                            />

                            <SelectInput
                                label="Line Manager "
                                value={data.line_manager_id}
                                onChange={(e) =>
                                    setData("line_manager_id", e.target.value)
                                }
                                options={lineManager}
                                error={errors.line_manager_id}
                            />

                            <SelectInput
                                label="Country *"
                                value={data.country_id}
                                onChange={(e) =>
                                    setData("country_id", e.target.value)
                                }
                                options={countries}
                                error={errors.country_id}
                            />

                            <SelectInput
                                label="Billing Type *"
                                value={data.billing_type_id}
                                onChange={(e) =>
                                    setData("billing_type_id", e.target.value)
                                }
                                options={billingtypes}
                                error={errors.billing_type_id}
                            />
                        </div>

                        <div className="pt-8 mt-4 border-t border-gray-800 flex items-center justify-end gap-4">
                            {/* Cancel Button */}
                            <Link
                                href={route("employee.index")} // ◄── Point this to your main employee directory route
                                className="px-5 py-2.5 rounded-lg text-sm font-medium text-gray-400 hover:text-white transition-colors"
                            >
                                Cancel
                            </Link>

                            {/* Submit Button */}
                            <button
                                type="submit"
                                disabled={processing}
                                className="bg-amber-500 hover:bg-amber-600 disabled:bg-amber-800/50 disabled:text-gray-500 disabled:cursor-not-allowed text-black font-semibold text-sm px-6 py-2.5 rounded-lg transition-all shadow-md cursor-pointer text-white"
                            >
                                {processing
                                    ? "Saving Record..."
                                    : "Commit Onboarding"}
                            </button>
                        </div>
                    </div>
                    {/* ACTION CONTROL BUTTONS */}
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
