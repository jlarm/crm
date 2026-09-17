import '../css/app.css';
import 'vue-sonner/style.css';
import './bootstrap';
import { createInertiaApp } from '@inertiajs/vue3';
import AuthLayout from '@/layouts/AuthLayout.vue';
import AppLayout from '@/layouts/AppLayout.vue';

const appName = import.meta.env.VITE_APP_NAME || 'CRM';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        if (name.startsWith('auth/')) {
            return AuthLayout;
        }
        return AppLayout;
    },
    progress: {
        color: '#4B5563',
    },
});
