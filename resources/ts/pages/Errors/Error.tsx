import { Link } from '@inertiajs/react'
import AuthLayout from '@/components/layouts/AuthLayout'
import AvaLogo from '@/components/shared/AvaLogo'

interface ErrorProps {
    status: number
    message?: string
    variant?: 'database'
}

const STATUS_CONTENT: Record<number, { icon: string; title: string; description: string }> = {
    401: {
        icon: 'bi-shield-lock',
        title: 'Authentication Required',
        description: 'You need to sign in before you can access this page.',
    },
    403: {
        icon: 'bi-slash-circle',
        title: 'Access Denied',
        description: "You don't have permission to access this page.",
    },
    404: {
        icon: 'bi-signpost-split',
        title: 'Page Not Found',
        description: "The page you're looking for doesn't exist or may have been moved.",
    },
    419: {
        icon: 'bi-hourglass-split',
        title: 'Page Expired',
        description: 'Your session expired for security reasons. Please try again.',
    },
    500: {
        icon: 'bi-exclamation-octagon',
        title: 'Server Error',
        description: 'Something went wrong on our end. Please try again in a moment.',
    },
    502: {
        icon: 'bi-hdd-network',
        title: 'Bad Gateway',
        description: "We're having trouble reaching one of our services right now.",
    },
    503: {
        icon: 'bi-tools',
        title: 'Service Unavailable',
        description: "The service is temporarily unavailable. Please try again shortly.",
    },
}

const DEFAULT_CONTENT = {
    icon: 'bi-exclamation-triangle',
    title: 'Unexpected Error',
    description: 'Something went wrong. Please try again.',
}

const VARIANT_CONTENT: Record<string, { icon: string; title: string; description: string }> = {
    database: {
        icon: 'bi-hdd-network',
        title: 'Temporarily Unavailable',
        description: "We're unable to complete your request right now. Please try again in a few minutes.",
    },
}

export default function Error({ status, message, variant }: ErrorProps) {
    const content = (variant && VARIANT_CONTENT[variant]) || STATUS_CONTENT[status] || DEFAULT_CONTENT

    return (
        <AuthLayout>
            <div className="text-center py-3">
                <div className="d-flex justify-content-center mb-4">
                    <AvaLogo size={40} showText />
                </div>

                <div className="icon-tile text-ava-secondary mx-auto mb-4" style={{ width: 72, height: 72 }}>
                    <i className={`bi ${content.icon}`} style={{ fontSize: 32 }} />
                </div>

                <p className="text-ava-muted text-tiny fw-semibold mb-1 text-uppercase">Error {status}</p>
                <h3 className="mb-2">{content.title}</h3>
                <p className="text-ava-muted mb-4">{message || content.description}</p>

                <div className="d-flex justify-content-center gap-2">
                    <Link href="/" className="btn btn-primary">
                        <i className="bi bi-house me-1" />
                        Go to Homepage
                    </Link>
                    <button type="button" className="btn btn-outline-primary" onClick={() => window.history.back()}>
                        <i className="bi bi-arrow-left me-1" />
                        Go Back
                    </button>
                </div>
            </div>
        </AuthLayout>
    )
}
