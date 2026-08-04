import React, { useState } from "react";
import { Link, usePage } from "@inertiajs/react";
import { ChevronRight } from "lucide-react"; // Modern chevron icon replacement

export default function SidebarGroup({ group }) {
    const [isOpen, setIsOpen] = useState(true);
    const { url } = usePage(); // Get current active URL path from Inertia

    return (
        <div className="mb-4">
            {/* Parent Module Header Trigger */}
            <button
                type="button"
                onClick={() => setIsOpen(!isOpen)}
                className="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wider hover:text-slate-200 transition focus:outline-none group cursor-pointer"
            >
                <span>{group.parent_name}</span>
                <ChevronRight
                    className={`w-3.5 h-3.5 text-slate-500 transition-transform duration-200 ${
                        isOpen
                            ? "rotate-90 text-blue-400"
                            : "group-hover:text-slate-300"
                    }`}
                />
            </button>

            {/* Child Modules List */}
            {isOpen && (
                <div className="mt-1 pl-2 space-y-1 border-l border-slate-800 ml-3">
                    {group.items.map((item) => {
                        const targetHref = `/dashboard/${item.custom_route_link || item.slug}`;

                        // Check if current route matches target route
                        const isActive = url.startsWith(targetHref);

                        return (
                            <Link
                                key={item.id}
                                href={targetHref}
                                className={`flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-all ${
                                    isActive
                                        ? "bg-blue-600/15 text-blue-400 font-semibold"
                                        : "text-slate-400 hover:bg-slate-800/60 hover:text-white"
                                }`}
                            >
                                {/* Bullet indicator highlights blue when active */}
                                <span
                                    className={`mr-2.5 text-[10px] ${
                                        isActive
                                            ? "text-blue-400 font-bold"
                                            : "text-slate-600"
                                    }`}
                                >
                                    ○
                                </span>
                                <span>{item.name}</span>
                            </Link>
                        );
                    })}
                </div>
            )}
        </div>
    );
}
