import React from 'react';
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '../Components/AuthenticatedLayout';
import { usePage, Link } from '@inertiajs/react';

export default function Dashboard({ auth, flash }) {
    return (
        <AuthenticatedLayout auth={auth}>
            {/* ─── EVERYTHING DOWN HERE BECOMES "CHILDREN" ─── */}
            <Head title="Dashboard" />

            <div className="bg-lumina-darkBg p-6 rounded-xl shadow-md border border-gray-800">
                <h1 className="text-2xl font-bold text-white mb-1">
                    Welcome back, {auth.user.name}
                </h1>
                <p className="text-sm text-gray-400">
                    Select an option from the sidebar module menu to manage your tasks.
                </p>
            </div>
        {/* ────────────────────────────────────────────── */}
        </AuthenticatedLayout>
    );
}
