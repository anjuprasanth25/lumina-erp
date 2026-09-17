import React from "react";
import { Head, useForm, Link } from "@inertiajs/react";
import AuthenticatedLayout from "../../Components/AuthenticatedLayout";
import TextInput from "../../Components/TextInput";
import SelectInput from "../../Components/SelectInput";

export default function User({ auth, lookups = {} }) {
    const {
        employees = [],
        companies = [],
        roles = [],
        modules = [],
    } = lookups;

    const getDefaultPermissionRows = () => {
        //find the id of 'Standard User' role
        const standardrole = roles.find(
            (r) =>
                r.code === "standard" ||
                r.name.toLowerCase().includes("standard"),
        );
        const standardroleid = standardrole ? standardrole.id : "";
        const standardmodules = modules.filter(
            (mod) => mod.is_default == 1 || mod.is_default == true,
        );

        const permissions = standardmodules.map((module) => ({
            id: `perm-${Date.now()}-${Math.random()}`,
            module_id: module.id,
            role_id: standardroleid,
        }));

        return permissions;
    };

    const { data, setData, post, transform, processing, errors } = useForm({
        employee_id: "",
        name: "",
        email: "",
        company: "",
        designation: "",
        company_id: "",
        company_access_blocks: [
            {
                id: `company-${Date.now()}-${Math.random()}`,
                company_id: "",
                permissions: getDefaultPermissionRows(),
            },
        ],
    });

    const addCompanyBlock = () => {
        setData("company_access_blocks", [
            ...data.company_access_blocks,
            {
                id: `company-${Date.now()}-${Math.random()}`,
                company_id: "",
                permissions: [
                    {
                        id: `perm-${Date.now()}-${Math.random()}`,
                        module_id: "",
                        role_id: "",
                    },
                ],
            },
        ]);
    };

    const removeCompanyBlock = (keyToremove) => {
        const updated = data.company_access_blocks.filter(
            (item) => item.id !== keyToremove,
        );
        setData("company_access_blocks", updated);
    };

    const addPermBlock = (companyBlockId) => {
        const updated = data.company_access_blocks.map((block) => {
            if (block.id === companyBlockId) {
                return {
                    ...block,
                    permissions: [
                        ...block.permissions,
                        {
                            id: `perm-${Date.now()}-${Math.random()}`,
                            module_id: "",
                            role_id: "",
                        },
                    ],
                };
            }
            return block;
        });
        setData("company_access_blocks", updated);
    };

    const removePermBlock = (companykeyToremove, permkeyToRemove) => {
        const updated = data.company_access_blocks.map((block) => {
            if (block.id === companykeyToremove) {
                return {
                    ...block,
                    permissions: block.permissions.filter(
                        (item) => item.id != permkeyToRemove,
                    ),
                };
            }
            return block;
        });
        setData("company_access_blocks", updated);
    };

    const handleSelectChange = (
        companyBlockId,
        permRowId = null,
        field,
        value,
    ) => {
        const updated = data.company_access_blocks.map((block) => {
            if (block.id === companyBlockId) {
                if (permRowId == null) {
                    return { ...block, [field]: value };
                }
                const updatedPerms = block.permissions.map((perm) => {
                    if (perm.id === permRowId) {
                        return {
                            ...perm,
                            [field]: value,
                        };
                    }
                    return perm;
                });
                return { ...block, permissions: updatedPerms };
            }
            return block;
        });
        setData("company_access_blocks", updated);
    };

    const isDefaultModule = (module_id) => {
        if (!module_id) return false;
        return modules.some(
            (m) =>
                m.id == module_id &&
                (m.is_default == 1 || m.is_default === true),
        );
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        const cleanedBlocks = data.company_access_blocks.filter(
            (block) =>
                block.permissions.length > 0 ||
                user?.employee?.company_id == block.company_id,
        );

        transform((data) => ({
            ...data,
            company_access_blocks: data.company_access_blocks.filter(
                (block) => block.permissions && block.permissions.length > 0,
            ),
        }));

        post(route("user.store"), {
            preserveScroll: true, // Prevents sudden window jumping if errors return
            onSuccess: () => {
                // Optional: Add a flash notification or tracking logic here
            },
            onError: (errors) => {
                // Scroll smoothly to the top of the window
                window.scrollTo({ top: 0, behavior: "smooth" });
            },
        });
    };

    const selectedCompanyIds = data.company_access_blocks
        .map((b) => String(b.company_id))
        .filter((id) => id != "");

    // Helper function to return available companies for a specific block
    const getAvailableCompanies = (currentBlockId, currentCompanyId) => {
        return companies.filter((company) => {
            const companyIdStr = String(company.id);

            // Always keep the company currently selected in THIS block
            if (companyIdStr === String(currentCompanyId)) {
                return true;
            }

            // Exclude if selected in ANY other block
            return !selectedCompanyIds.includes(companyIdStr);
        });
    };
    const getAvailableModulesForBlock = (block, currentModuleId) => {
        const selectedModuleIds = (block.permissions || [])
            .map((perm) => String(perm.module_id))
            .filter((id) => id !== "" && id !== "undefined");
        return modules.filter((mod) => {
            const modIdStr = String(mod.id);

            if (modIdStr === String(currentModuleId)) {
                return true;
            }

            return !selectedModuleIds.includes(modIdStr);
        });
    };

    return (
        <AuthenticatedLayout auth={auth}>
            <Head title="User Onboarding" />

            <div className="max-w-5xl mx-auto p-6 text-gray-200 bg-lumina-darkBg">
                <h1 className="text-2xl font-bold text-white mb-6">
                    User Onboarding
                </h1>
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
                    {/* CARD CONTAINER */}
                    <div className="bg-slate-900/60 backdrop-blur-md border border-slate-800 rounded-xl p-6 shadow-xl">
                        <h3 className="mb-2 font-bold text-white">
                            Employee Information
                        </h3>
                        <div className="space-y-6">
                            <div>
                                <SelectInput
                                    label="Employee"
                                    options={employees}
                                    value={data.employee_id}
                                    onChange={(e) => {
                                        const selectedId = e.target.value;
                                        const selectedEmployee = employees.find(
                                            (emp) => emp.id == selectedId,
                                        );
                                        const empCompanyId =
                                            selectedEmployee?.company_id || "";
                                        setData("company_id", empCompanyId);
                                        setData((prevData) => {
                                            // Map over current blocks and update the first card's company_id automatically
                                            const updatedBlocks =
                                                data.company_access_blocks.map(
                                                    (block, index) => {
                                                        if (index == 0) {
                                                            return {
                                                                ...block,
                                                                company_id:
                                                                    empCompanyId,
                                                            };
                                                        }
                                                        return block;
                                                    },
                                                );

                                            return {
                                                ...prevData,
                                                employee_id: selectedId,
                                                name:
                                                    selectedEmployee?.name ||
                                                    "",
                                                email:
                                                    selectedEmployee?.email ||
                                                    "",
                                                company:
                                                    selectedEmployee?.company
                                                        ?.name || "",
                                                designation: `${
                                                    selectedEmployee
                                                        ?.designation?.name ||
                                                    ""
                                                } - ${selectedEmployee?.department?.name || ""}`,
                                                company_access_blocks:
                                                    updatedBlocks, // Sets company dropdown value automatically!
                                            };
                                        });
                                    }}
                                />
                            </div>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <TextInput
                                    label="Email"
                                    value={data.email || ""}
                                    readOnly
                                    disabled
                                />

                                <TextInput
                                    label="Company"
                                    value={data.company || ""}
                                    readOnly
                                    disabled
                                />
                            </div>
                        </div>
                    </div>

                    <div className="bg-slate-900/60 backdrop-blur-md border border-slate-800 rounded-xl p-6 shadow-xl">
                        <h3 className="mb-4 font-bold text-white text-base">
                            Company & Module Permissions
                        </h3>
                        {/* REPEATER LISTING */}
                        <div className="space-y-6">
                            {data.company_access_blocks.map((block, index) => {
                                const isPrimaryCompanyBlock = index === 0;
                                return (
                                    <div
                                        key={block.id}
                                        className="p-5 border border-slate-700/80 rounded-xl bg-slate-950/50 shadow-inner relative"
                                    >
                                        {/* COMPANY HEADER WITH DELETE ICON */}
                                        <div className="flex justify-between items-center mb-4">
                                            <span className="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                                Company Profile
                                            </span>

                                            {/* DELETE COMPANY ICON BUTTON */}
                                            {data.company_access_blocks.length >
                                                1 &&
                                                index > 0 && (
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            removeCompanyBlock(
                                                                block.id,
                                                            )
                                                        }
                                                        className="text-gray-400 hover:text-red-400 p-1 rounded-lg transition-colors flex items-center gap-1 text-xs font-medium"
                                                        title="Delete Company Block"
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
                                        <div className="mb-5 max-w-xl">
                                            {/* Company Dropdown Selection */}
                                            <SelectInput
                                                label="Company"
                                                id={`company-${block.id}`}
                                                title="Company"
                                                options={getAvailableCompanies(
                                                    block.id,
                                                    block.company_id,
                                                )}
                                                disabled={isPrimaryCompanyBlock}
                                                value={block.company_id}
                                                onChange={(e) =>
                                                    handleSelectChange(
                                                        block.id,
                                                        null,
                                                        "company_id",
                                                        e.target.value,
                                                    )
                                                }
                                            />
                                        </div>
                                        {/* Nested Exceptions Loop */}
                                        <div className="mt-4 pt-4 border-t border-slate-700/60 space-y-3">
                                            {block.permissions.map((perm) => {
                                                const hideDelete =
                                                    isPrimaryCompanyBlock &&
                                                    isDefaultModule(
                                                        perm.module_id,
                                                    );
                                                const showDelete = !hideDelete;
                                                return (
                                                    <div
                                                        key={perm.id}
                                                        className="flex items-end gap-3 my-3 mt-3"
                                                    >
                                                        <SelectInput
                                                            label="Module"
                                                            id={`role-${perm.id}`}
                                                            title="Module"
                                                            options={getAvailableModulesForBlock(
                                                                block,
                                                                perm.module_id,
                                                            )}
                                                            disabled={
                                                                hideDelete
                                                            }
                                                            value={
                                                                perm.module_id
                                                            }
                                                            onChange={(e) =>
                                                                handleSelectChange(
                                                                    block.id,
                                                                    perm.id,
                                                                    "module_id",
                                                                    e.target
                                                                        .value,
                                                                )
                                                            }
                                                        />

                                                        <SelectInput
                                                            label="Role"
                                                            title="Role"
                                                            id={`role-${perm.id}`}
                                                            options={roles}
                                                            value={perm.role_id}
                                                            onChange={(e) =>
                                                                handleSelectChange(
                                                                    block.id,
                                                                    perm.id,
                                                                    "role_id",
                                                                    e.target
                                                                        .value,
                                                                )
                                                            }
                                                        />

                                                        {/* DELETE MODULE BUTTON */}
                                                        <div className="w-8 flex-shrink-0 flex items-center justify-center h-[42px]">
                                                            {showDelete && (
                                                                <button
                                                                    type="button"
                                                                    onClick={() =>
                                                                        removePermBlock(
                                                                            block.id,
                                                                            perm.id,
                                                                        )
                                                                    }
                                                                    className="text-gray-200 hover:text-red-400 p-2 font-bold transition-colors"
                                                                    title="Delete Company"
                                                                >
                                                                    ✕
                                                                </button>
                                                            )}
                                                        </div>
                                                    </div>
                                                );
                                            })}

                                            <div className="mt-6 flex justify-center">
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        addPermBlock(block.id)
                                                    }
                                                    className="bg-[#1f2433] hover:bg-[#2b3247] border border-gray-700 text-white font-medium text-xs px-4 py-2 rounded-lg transition-all shadow-sm"
                                                >
                                                    Add Module
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>

                        {/* ADD ACCESS BUTTON */}
                        <div className="mt-6 flex justify-center">
                            <button
                                type="button"
                                onClick={addCompanyBlock}
                                className="bg-[#1f2433] hover:bg-[#2b3247] border border-gray-700 text-white font-medium text-xs px-4 py-2 rounded-lg transition-all shadow-sm"
                            >
                                Add Company
                            </button>
                        </div>
                        {/* FORM ACTION BUTTONS */}
                        <div className="mt-8 flex items-center justify-end gap-3 pt-6 border-t border-gray-800">
                            {/* Cancel Button */}
                            <Link
                                href={route("user.index")} // ◄── Point this to your main employee directory route
                                className="px-5 py-2.5 rounded-lg text-sm font-medium text-gray-400 hover:text-white transition-colors"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                onClick={handleSubmit}
                                disabled={processing} // Prevents clicks while processing
                                className={`px-6 py-2.5 rounded-lg text-white font-semibold text-sm transition-all shadow-md ${
                                    processing
                                        ? "bg-blue-400 cursor-not-allowed opacity-75" // Disabled styling
                                        : "bg-blue-600 hover:bg-blue-500 active:scale-95" // Active styling
                                }`}
                            >
                                {processing ? (
                                    <span className="flex items-center gap-2">
                                        {/* Optional SVG loading spinner */}
                                        <svg
                                            className="animate-spin h-4 w-4 text-white"
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                        >
                                            <circle
                                                className="opacity-25"
                                                cx="12"
                                                cy="12"
                                                r="10"
                                                stroke="currentColor"
                                                strokeWidth="4"
                                            ></circle>
                                            <path
                                                className="opacity-75"
                                                fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                            ></path>
                                        </svg>
                                        Saving Record...
                                    </span>
                                ) : (
                                    "Commit Onboarding"
                                )}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
