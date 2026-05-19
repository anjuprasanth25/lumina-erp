import React from 'react';
import { Head } from '@inertiajs/react';


export default function Dashboard({ auth }) {

    const username = auth.user?.name || 'Guest';
    return (
        <div style={{ padding: '40px', fontFamily: 'sans-serif' }}>
            <Head title="Dashboard" />

            <h1>Welcome to Lumina ERP Dashboard</h1>
            <p>Hello, {username}!</p>
            <div style={{ marginTop: '20px', padding: '20px', background: '#f3f4f6', borderRadius: '8px' }}>
                <h3>Employee Self-Service (React + Inertia)</h3>
                <p>This is your custom frontend view.</p>
            </div>
        </div>
    );
}
