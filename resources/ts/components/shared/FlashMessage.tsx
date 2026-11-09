import { useEffect, useState } from 'react'
import { usePage } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import type { SharedProps } from '@/types'

const VARIANT_MAP: Record<string, string> = {
    success: 'success',
    error: 'danger',
    warning: 'warning',
    info: 'info',
}

export default function FlashMessage() {
    const { t } = useTranslation('common')
    const { flash } = usePage<SharedProps>().props
    const [visible, setVisible] = useState(true)

    useEffect(() => {
        setVisible(true)
        const timer = setTimeout(() => setVisible(false), 4000)

        return () => clearTimeout(timer)
    }, [flash])

    if (!flash?.message || !visible) {
        return null
    }

    const variant = VARIANT_MAP[flash?.type ?? 'info'] ?? 'info'

    return (
        <div className="toast-popup-wrapper">
            <div className={`alert alert-${variant} alert-dismissible fade show toast-popup shadow`} role="alert">
                {flash.message}
                <button type="button" className="btn-close" aria-label={t('actions.close')} onClick={() => setVisible(false)} />
            </div>
        </div>
    )
}
