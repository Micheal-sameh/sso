export interface User {
    id: number
    uuid: string
    membership_code: string
    name: string
    email: string
    avatar: string | null
    role: 'superadmin' | 'user'
    is_active: boolean
    is_admin: boolean
    email_verified_at: string | null
}

export interface UserMfa {
    id: number
    is_enabled: boolean
}

export interface OAuthClient {
    id: string
    name: string
    redirect: string
    personal_access_client: boolean
    password_client: boolean
    revoked: boolean
}

export interface ClientMeta {
    id: number
    oauth_client_id: string
    app_name: string
    app_logo_url: string | null
    homepage_url: string | null
    is_internal: boolean
    is_verified: boolean
    oauth_client: OAuthClient
}

export interface Token {
    id: string
    user_id: number
    client_id: string
    name: string | null
    scopes: string[]
    revoked: boolean
    created_at: string
    expires_at: string
    client: OAuthClient
    user?: User
}

export interface PaginatedData<T> {
    data: T[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    links: { url: string | null; label: string; active: boolean }[]
}

export interface FlashMessage {
    type: 'success' | 'error' | 'warning' | 'info' | null
    message: string | null
}

export interface SharedProps {
    [key: string]: unknown
    auth: { user: User | null }
    flash: FlashMessage
    csrf_token: string
    errors: Record<string, string>
}
