import React from "react";

export default function SelectInput({
    label,
    id,
    error,
    options = [],
    disabled = false,
    ...props
}) {
    const fieldId =
        id ||
        `input-${label?.replace(/\s+/g, "-").toLowerCase()}-${Math.random().toString(36).substring(2, 7)}`;
    return (
        <div className="w-full">
            {label && (
                <label
                    htmlFor={fieldId}
                    className={`block text-sm mb-2 ${
                        disabled ? "text-gray-500" : "text-gray-400"
                    }`}
                >
                    {label}
                </label>
            )}

            <select
                id={fieldId}
                disabled={disabled}
                {...props}
                style={{
                    backgroundColor: "#1c273e",
                    color: "#ffffff",
                    colorScheme: "dark",
                }}
                className="w-full border border-gray-800 rounded-lg px-4 py-2.5 text-sm outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors"
            >
                <option value="" className="bg-[#131b2e] text-slate-400">
                    Select Input
                </option>
                {options.map((opt, index) => (
                    <option
                        value={opt.id}
                        key={opt.id || index}
                        className="bg-[#131b2e] text-white py-1"
                    >
                        {opt.name}
                    </option>
                ))}
            </select>
            {error && (
                <p className="text-rose-400 text-xs mt-1 font-medium tracking-wide">
                    {error}
                </p>
            )}
        </div>
    );
}
