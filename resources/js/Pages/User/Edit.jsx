import React from "react";
import { Head, useForm, Link } from "@inertiajs/react";
import AuthenticatedLayout from "../../Components/AuthenticatedLayout";
import TextInput from "../../Components/TextInput";
import SelectInput from "../../Components/SelectInput";
import { route } from "ziggy-js";
import { ArrowLeft } from "lucide-react";

export default function Edit({
    auth,
    user,
    companyAccessBlocks,
    companies,
    roles,
    modules,
}) {
    const { data, setData, put, transform, processing, errors } = useForm({
        company_access_blocks: companyAccessBlocks,
    });

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

                const updatedperm = block.permissions.map((permblock) => {
                    if (permblock.id === permRowId) {
                        return {
                            ...permblock,
                            [field]: value,
                        };
                    }
                    return permblock;
                });

                return { ...block, permissions: updatedperm };
            }
            return block;
        });
        setData("company_access_blocks", updated);
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        // Tell Inertia how to mutate the data right before sending
        transform((data) => ({
            ...data,
            company_access_blocks: data.company_access_blocks.filter(
                (block) => block.permissions && block.permissions.length > 0,
            ),
        }));

        // Now call put with standard arguments
        put(route("user.update", user.id), {
            preserveScroll: true,
            onError: () => {
                // Smoothly scroll the window to top
                window.scrollTo({ top: 0, behavior: "smooth" });
            },
        });
    };

    const isDefaultModule = (module_id) => {
        if (!module_id) return false;
        return modules.some(
            (m) =>
                m.id == module_id &&
                (m.is_default == 1 || m.is_default === true),
        );
    };

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
            if (block.id == companyBlockId) {
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
            if (block.id == companykeyToremove) {
                return {
                    ...block,
                    permissions: block.permissions.filter(
                        (item) => item.id !== permkeyToRemove,
                    ),
                };
            }
            return block;
        });
        setData("company_access_blocks", updated);
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
            <div className="py-12 bg-lumina-darkBg min-h-screen text-gray-200">
                <Head title={`Edit User - ${user.name}`} />
                <div className="max-w-4xl mx-auto">
                    {/* HEADER LOG */}
                    <div className="flex justify-between items-center mb-6">
                        <h2 className="text-xl font-semibold tracking-wide text-white">
                            Modify User Profile:{" "}
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
                        <div className="bg-slate-900/60 backdrop-blur-md border border-slate-800 rounded-xl p-6 shadow-xl">
                            <h3 className="mb-4 font-bold text-white text-base">
                                Company & Module Permissions
                            </h3>

                            {/* REPEATER LISTING */}
                            <div className="space-y-6">
                                {data.company_access_blocks.map(
                                    (block, index) => {
                                        const isPrimaryCompanyBlock =
                                            user?.employee?.company_id ==
                                            block?.company_id;

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
                                                    {!isPrimaryCompanyBlock && (
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
                                                                    strokeWidth={
                                                                        2
                                                                    }
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                                />
                                                            </svg>
                                                        </button>
                                                    )}
                                                </div>
                                                <div className="mb-5 max-w-xl">
                                                    <SelectInput
                                                        label="Company"
                                                        id={`${block.id}`}
                                                        title="company"
                                                        options={getAvailableCompanies(
                                                            block.id,
                                                            block.company_id,
                                                        )}
                                                        value={block.company_id}
                                                        disabled={
                                                            isPrimaryCompanyBlock
                                                        }
                                                        onChange={(e) => {
                                                            handleSelectChange(
                                                                block.id,
                                                                null,
                                                                "company_id",
                                                                e.target.value,
                                                            );
                                                        }}
                                                    />
                                                </div>
                                                {/* Nested Exceptions Loop */}
                                                <div className="mt-4 pt-4 border-t border-slate-700/60 space-y-3">
                                                    {block.permissions.map(
                                                        (perm) => {
                                                            const hideDelete =
                                                                isPrimaryCompanyBlock &&
                                                                isDefaultModule(
                                                                    perm.module_id,
                                                                );

                                                            const showDelete =
                                                                !hideDelete;
                                                            return (
                                                                <div
                                                                    key={
                                                                        perm.id
                                                                    }
                                                                    className="flex items-end gap-3 my-3 mt-3"
                                                                >
                                                                    <SelectInput
                                                                        label="Module"
                                                                        id={`module-${perm.id}`}
                                                                        title="Module"
                                                                        disabled={
                                                                            hideDelete
                                                                        }
                                                                        options={getAvailableModulesForBlock(
                                                                            block,
                                                                            perm.module_id,
                                                                        )}
                                                                        onChange={(
                                                                            e,
                                                                        ) => {
                                                                            handleSelectChange(
                                                                                block.id,
                                                                                perm.id,
                                                                                "module_id",
                                                                                e
                                                                                    .target
                                                                                    .value,
                                                                            );
                                                                        }}
                                                                        value={
                                                                            perm.module_id
                                                                        }
                                                                    />

                                                                    <SelectInput
                                                                        label="Role"
                                                                        id={`role-${perm.id}`}
                                                                        title="Role"
                                                                        options={
                                                                            roles
                                                                        }
                                                                        onChange={(
                                                                            e,
                                                                        ) => {
                                                                            handleSelectChange(
                                                                                block.id,
                                                                                perm.id,
                                                                                "role_id",
                                                                                e
                                                                                    .target
                                                                                    .value,
                                                                            );
                                                                        }}
                                                                        value={
                                                                            perm.role_id
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
                                                        },
                                                    )}

                                                    <div className="mt-6 flex justify-center">
                                                        <button
                                                            type="button"
                                                            onClick={() =>
                                                                addPermBlock(
                                                                    block.id,
                                                                )
                                                            }
                                                            className="bg-[#1f2433] hover:bg-[#2b3247] border border-gray-700 text-white font-medium text-xs px-4 py-2 rounded-lg transition-all shadow-sm"
                                                        >
                                                            Add Module
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        );
                                    },
                                )}
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

                            <div className="flex items-center justify-end gap-4 pt-8 border-t border-gray-800 mt-4">
                                {/* Cancel Button */}
                                <Link
                                    href={route("user.index")} // ◄── Point this to your main employee directory route
                                    className="px-5 py-2.5 rounded-lg text-sm font-medium text-gray-400 hover:text-white transition-colors"
                                >
                                    Cancel
                                </Link>

                                {/* Submit Button */}
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="bg-blue-600 hover:bg-blue-500 disabled:bg-amber-800/50 disabled:text-gray-500 disabled:cursor-not-allowed text-black font-semibold text-sm px-6 py-2.5 rounded-lg transition-all shadow-md cursor-pointer"
                                >
                                    {processing ? "Saving Record..." : "Save"}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
