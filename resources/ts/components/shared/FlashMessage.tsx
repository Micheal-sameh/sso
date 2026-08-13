import { useEffect, useState } from 'react'
import { usePage } from '@inertiajs/react'
import type { SharedProps } from '@/types'

const VARIANT_MAP: Record<string, string> = {
    success: 'success',
    error: 'danger',
    warning: 'warning',
    info: 'info',
}

export default function FlashMessage() {
    const { flash } = usePage<SharedProps>().props
    const [visible, setVisible] = useState(true)

    useEffect(() => {
        setVisible(true)
        const timer = setTimeout(() => setVisible(false), 4000)

        return () => clearTimeout(timer)
    }, [flash.message])

    if (!flash.message || !visible) {
        return null
    }

    const variant = VARIANT_MAP[flash.type ?? 'info'] ?? 'info'

    return (
        <div className={`alert alert-${variant} alert-dismissible fade show`} role="alert">
            {flash.message}
            <button type="button" className="btn-close" aria-label="Close" onClick={() => setVisible(false)} />
        </div>
    )
}
