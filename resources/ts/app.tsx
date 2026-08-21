import { createInertiaApp } from '@inertiajs/react'
import { createRoot } from 'react-dom/client'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { ConfirmProvider } from '@/components/shared/ConfirmDialog'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap-icons/font/bootstrap-icons.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import '@/styles/app.css'

createInertiaApp({
    title: (title) => (title ? `${title} - Avarewase Auth` : 'Avarewase Auth'),
    resolve: (name) =>
        resolvePageComponent(`./pages/${name}.tsx`, import.meta.glob('./pages/**/*.tsx')),
    setup({ el, App, props }) {
        createRoot(el).render(
            <ConfirmProvider>
                <App {...props} />
            </ConfirmProvider>,
        )
    },
})

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            // Installability/offline support is a progressive enhancement; a
            // failed registration (e.g. unsupported browser policy) shouldn't
            // block the app from working normally.
        })
    })
}
