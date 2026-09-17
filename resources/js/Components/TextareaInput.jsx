import React from "react";

export default function TextareaInput({
    label,
    id,
    error,
    rows = 3,
    ...props
}) {
    const fieldId =
        id || `textarea-${label.replace(/\s+/g, "-").toLowerCase()}`;

    return (
        <div>
            <label
                htmlFor={fieldId}
                className="block text-sm text-gray-400 mb-2"
            >
                {label}
            </label>
            <textarea
                id={fieldId}
                rows={rows}
                {...props}
                style={{
                    backgroundColor: "#1c273e",
                    color: "#ffffff",
                    colorScheme: "dark",
                }}
                className="w-full border border-[#2f3e5e] text-white rounded-lg px-4 py-2.5 text-sm placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all resize-y"
            />
            {error && <p className="!text-red-500 text-xs mt-1">{error}</p>}
        </div>
    );
}
