import { Link } from '@inertiajs/react'
import AvaLogo from '@/components/shared/AvaLogo'

const FEATURES = [
    {
        icon: 'bi-shield-lock',
        title: 'Single Sign-On',
        description: 'One set of credentials for every connected application, built on OAuth2 and OpenID Connect.',
    },
    {
        icon: 'bi-phone',
        title: 'Two-Factor Authentication',
        description: 'Protect every account with TOTP-based two-factor authentication and one-time recovery codes.',
    },
    {
        icon: 'bi-people',
        title: 'Centralized Directory',
        description: 'Manage every member, their stage, and their role from a single admin console.',
    },
    {
        icon: 'bi-key',
        title: 'Client Access Control',
        description: 'Grant connected apps access to everyone, a specific stage, or hand-picked individuals.',
    },
    {
        icon: 'bi-clock-history',
        title: 'Self-Service Recovery',
        description: "One-time, time-limited links for password resets and profile photo updates — no support ticket required.",
    },
    {
        icon: 'bi-diagram-3',
        title: 'Built for Integrations',
        description: 'A standards-based identity provider that any OAuth2/OIDC client app can plug into in minutes.',
    },
]

export default function Welcome() {
    return (
        <div className="welcome-page">
            <header className="welcome-header d-flex align-items-center justify-content-between px-4 py-3">
                <AvaLogo size={36} showText />
                <Link href="/login" className="btn btn-primary btn-sm">
                    Sign In
                </Link>
            </header>

            <section className="welcome-hero text-center py-5 px-3">
                <span className="role-chip mb-3 d-inline-block">Identity &amp; Access Management</span>
                <h1 className="mb-3">One identity for every Avarewase app</h1>
                <p className="text-ava-muted mx-auto mb-4 max-w-560">
                    Avarewase Auth is the central sign-on provider for your organization &mdash; a single secure
                    account that connects to every app you use, protected by two-factor authentication and managed
                    from one admin console.
                </p>
                <div className="d-flex gap-2 justify-content-center flex-wrap">
                    <Link href="/login" className="btn btn-primary">
                        Sign In to Your Account
                    </Link>
                    <a href="#features" className="btn btn-outline-primary">
                        Learn More
                    </a>
                </div>
            </section>

            <section id="features" className="container py-5">
                <div className="row g-4">
                    {FEATURES.map((feature) => (
                        <div key={feature.title} className="col-md-6 col-lg-4">
                            <div className="ava-card p-4 h-100">
                                <div className="icon-tile text-ava-secondary mb-3">
                                    <i className={`bi ${feature.icon}`} />
                                </div>
                                <h5 className="mb-2">{feature.title}</h5>
                                <p className="text-ava-muted small mb-0">{feature.description}</p>
                            </div>
                        </div>
                    ))}
                </div>
            </section>

            <footer className="text-center text-ava-muted text-tiny py-4 border-top">
                &copy; {new Date().getFullYear()} Avarewase Auth. All rights reserved.
            </footer>
        </div>
    )
}
