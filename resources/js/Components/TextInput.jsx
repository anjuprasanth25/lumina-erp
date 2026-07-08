import React from "react";

export default function TextInput({label, id, error, ...props}){
    const fieldId = id || `input-${label.replace(/\s+/g, '-').toLowerCase()}`;
    return(
        <div>
            <label htmlFor={fieldId} className="block text-sm text-gray-400 mb-2">{label}</label>
            <input
                id={fieldId}
                {...props}
                /* 🌟 FIXED style attribute override to decisively clear the backend form white reset */
                style={{ backgroundColor: '#1c1c1e', color: '#ffffff' }}
                className="w-full bg-[#1c1c1e] text-white border border-gray-800 rounded-lg px-4 py-2.5 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
            />
            {error && <p className="!text-red-500 text-xs mt-1">{error}</p>}
        </div>
    );

}
