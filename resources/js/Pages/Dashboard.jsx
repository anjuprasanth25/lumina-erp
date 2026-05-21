import React from 'react';
import { Head } from '@inertiajs/react';
import { usePage, Link } from '@inertiajs/react';

export default function Dashboard() {
    const {flash , auth} = usePage().props;

    return (
        <div className="p-6">
            <Head title="Dashboard" />
           {/* Success Banner */}
            { flash.success &&
                <div style={{ backgroundColor: '#d4edda', color: '#155724', padding: '12px', borderRadius: '4px', marginBottom: '20px' }}>{ flash.success }</div>
            }

            <h1> Welcome to your dashboard, {auth.user.name}</h1>
            {/* Leave request link */}
            <div className="mt-4">
                <Link href={route('leave-requests.create')}
                className="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700"
                style={{ textDecoration: 'none', display: 'inline-block' }}>
                    Apply for Leave
                </Link>
            </div>

        </div>
    );
}
