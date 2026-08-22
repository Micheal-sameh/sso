import { createInertiaApp } from '@inertiajs/react'
import { createRoot } from 'react-dom/client'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { I18nextProvider } from 'react-i18next'
import { ConfirmProvider } from '@/components/shared/ConfirmDialog'
import i18n from '@/i18n'
import type { SharedProps } from '@/types'
import 'bootstrap-icons/font/bootstrap-icons.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import '@/styles/app.css'

if (document.documentElement.dir === 'rtl') {
    import('bootstrap/dist/css/bootstrap.rtl.min.css')
} else {
    import('bootstrap/dist/css/bootstrap.min.css')
}

createInertiaApp({
    title: (title) => (title ? `${title} - Avarewase Auth` : 'Avarewase Auth'),
    resolve: (name) =>
        resolvePageComponent(`./pages/${name}.tsx`, import.meta.glob('./pages/**/*.tsx')),
    setup({ el, App, props }) {
        const { locale } = props.initialPage.props as unknown as SharedProps
        i18n.changeLanguage(locale)

        createRoot(el).render(
            <I18nextProvider i18n={i18n}>
                <ConfirmProvider>
                    <App {...props} />
                </ConfirmProvider>
            </I18nextProvider>,
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
