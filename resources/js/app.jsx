console.log("Vite is loading app.jsx!");
import './bootstrap';
import '../css/app.css';

import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

const el = document.getElementById('app');

if(el){

    // Manually parse the data-page attribute
    const pageData = JSON.parse(el.dataset.page);

    createInertiaApp({
        page: pageData,
        title: (title) => `${title} - Lumina ERP`,
        resolve: (name) => resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob('./Pages/**/*.jsx')),
        setup({ el, App, props }) {
            createRoot(el).render(<App {...props} />);
        },
        progress: {
            color: '#4B5563',
        },
    });
}

