import { createContext, useCallback, useContext, useMemo, useRef, useState } from 'react'
import type { ReactNode } from 'react'
import { useTranslation } from 'react-i18next'

interface ConfirmOptions {
    title?: string
    confirmLabel?: string
    cancelLabel?: string
    variant?: 'danger' | 'primary'
}

interface ConfirmState extends ConfirmOptions {
    message: string
}

type ConfirmFn = (message: string, options?: ConfirmOptions) => Promise<boolean>

const ConfirmContext = createContext<ConfirmFn | null>(null)

export function useConfirm(): ConfirmFn {
    const confirm = useContext(ConfirmContext)
    if (!confirm) {
        throw new Error('useConfirm must be used within a ConfirmProvider')
    }
    return confirm
}

export function ConfirmProvider({ children }: { children: ReactNode }) {
    const { t } = useTranslation('common')
    const [state, setState] = useState<ConfirmState | null>(null)
    const resolver = useRef<((value: boolean) => void) | null>(null)

    const confirm = useCallback<ConfirmFn>((message, options) => {
        return new Promise((resolve) => {
            resolver.current = resolve
            setState({ message, ...options })
        })
    }, [])

    const settle = (value: boolean) => {
        resolver.current?.(value)
        resolver.current = null
        setState(null)
    }

    const contextValue = useMemo(() => confirm, [confirm])

    return (
        <ConfirmContext.Provider value={contextValue}>
            {children}
            {state && (
                <div className="modal d-block modal-backdrop-custom" tabIndex={-1}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content">
                            <div className="modal-body pt-4">
                                <div className="d-flex gap-3">
                                    <div className={state.variant === 'primary' ? 'confirm-icon' : 'danger-icon'}>
                                        <i className={`bi ${state.variant === 'primary' ? 'bi-question-circle' : 'bi-exclamation-triangle'}`} />
                                    </div>
                                    <div>
                                        <h5 className="mb-1">{state.title ?? t('confirmDialog.defaultTitle')}</h5>
                                        <p className="text-ava-muted mb-0">{state.message}</p>
                                    </div>
                                </div>
                            </div>
                            <div className="modal-footer">
                                <button type="button" className="btn btn-outline-primary" onClick={() => settle(false)}>
                                    {state.cancelLabel ?? t('actions.cancel')}
                                </button>
                                <button
                                    type="button"
                                    className={`btn ${state.variant === 'primary' ? 'btn-primary' : 'btn-danger'}`}
                                    onClick={() => settle(true)}
                                >
                                    {state.confirmLabel ?? t('actions.confirm')}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </ConfirmContext.Provider>
    )
}
