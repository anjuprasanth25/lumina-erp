import React from "react";


export default function SelectInput({label, id, error, options = [],...props}){
    const fieldId = id || `input-${label?.replace(/\s+/g, '-').toLowerCase()}`;
    return(
        <div className="w-full">
            {label && (
                <label htmlFor={fieldId} className="block text-sm text-gray-400 mb-2">{label}</label>
            )}

            <select id={fieldId} {...props}
                style={{ backgroundColor: '#1c1c1e', color: '#ffffff' }}
                className="w-full border border-gray-800 rounded-lg px-4 py-2.5 text-sm outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors"
            >
                <option value="">Select Input</option>
                { options.map((opt, index) => (
                    <option value={opt.id } key={opt.id || index}>{opt.name}</option>
                ))}
            </select>
             {error && <p className="!text-black text-xs mt-1 font-medium tracking-wide">
                { error }
            </p>}
        </div>
    );
}
