import React, { useState } from "react";
import { useForm } from "@inertiajs/react";
import SidebarGroup from "./SidebarGroup";

export default function AuthenticatedLayout({auth, children}){
    const [dropdownOpen, setDropdownOpen] = useState(false);
    const { post } = useForm();

    const handleLogout = (e) => {
        e.preventDefault();
        post(route('logout'));
    };

    return(
        <div className="min-h-screen bg-lumina-darkBg text-gray-100 flex flex-col font-sans">
            <header className="bg-lumina-panelBg text-white h-16 px-6 flex items-center justify-between shadow-md z-20">
                <div className="flex items-center space-x-3">
                    <span className="text-xl font-bold tracking-tight text-gray-100">Lumina ERP</span>
                </div>

                {/* Profile Avatar */}
                <div className="relative">
                    <button
                        onClick={() => setDropdownOpen(!dropdownOpen)}
                        className="w-9 h-9 bg-amber-500 text-slate-950 font-semibold rounded-full flex items-center justify-center text-sm uppercase border border-amber-400 focus:outline-none hover:bg-amber-400 transition"
                    >
                        {auth.user.initials}
                    </button>

                    {dropdownOpen && (
                        <div className="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 text-gray-800 border z-30">
                            <div className="px-4 py-2 border-b text-xs text-gray-500 truncate">
                                {auth.user.email}
                            </div>
                            <button
                                onClick={handleLogout}
                                className="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 text-red-600 font-medium flex items-center space-x-2"
                            >
                                <span>Sign out</span>
                            </button>
                        </div>
                    )}
                </div>

            </header>

            <div className="flex flex-1">
                {/* ─── DYNAMIC SIDEBAR ─── */}
                <aside className="w-64 bg-lumina-panelBg flex flex-col p-4 pt-6 shadow-xl">
                    {auth.menuStructure && auth.menuStructure.length > 0 ? (
                        auth.menuStructure.map((group, index) => (
                            <SidebarGroup key={index} group={group} />
                        ))
                    ) : (
                        <span className="text-xs text-gray-400 italic px-3">No modules assigned</span>
                    )}
                </aside>

                {/* ─── MAIN CONTENT CONTAINER ─── */}
                <main className="flex-1 p-8 bg-lumina-darkBg">
                    {children}
                </main>
            </div>
        </div>
    );

}
