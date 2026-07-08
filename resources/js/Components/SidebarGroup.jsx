import React, { useState } from 'react';
import { Link } from '@inertiajs/react';
import { route } from 'ziggy-js';

export default function SidebarGroup({ group }){
    // Keep individual sections open by default, just like Filament
    const [isOpen, setIsOpen] = useState(true);

    return (
        <div className="mb-4">
            {/* Parent Module Header Trigger */}
            <button
                onClick={() => setIsOpen(!isOpen)}
                className="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider hover:text-gray-600 transition focus:outline-none"
            >
                <span>{group.parent_name}</span>
                <span className={`transform transition-transform duration-200 ${isOpen ? 'rotate-90' : ''}`}>
                    ▶
                </span>
            </button>

            {/* Child Modules List */}
            {isOpen && (
                <div className="mt-1 pl-2 space-y-1 border-l border-gray-100 ml-3">
                    {group.items.map((item) => (
                        <Link
                            key={item.id}
                            href={`/dashboard/${item.custom_route_link || item.slug}`}
                            className="flex items-center px-3 py-2 rounded-lg text-sm font-medium text-gray-400 hover:bg-gray-800 hover:text-white transition"
                        >
                            <span className="mr-2 text-xs text-amber-500">○</span>
                            {item.name}
                        </Link>
                    ))}
                </div>
            )}
        </div>
    );
}
