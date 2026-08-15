export interface Stage {
    id: number
    name: string
    code: string
    sort_order: number
    users_count?: number
}

export interface UserStageSummary {
    id: number
    name: string
    code: string
}

export interface User {
    id: number
    uuid: string
    membership_code: string
    name: string
    email: string
    phone: string | null
    avatar: string | null
    role: 'superadmin' | 'admin' | 'user'
    is_active: boolean
    is_admin: boolean
    can_manage_admin_access: boolean
    stage: UserStageSummary
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
    access_mode: 'all' | 'stage' | 'specific'
    oauth_client: OAuthClient
    stages?: Stage[]
    allowed_users?: { id: number }[]
}

export interface SpecificClientAccess {
    id: string
    app_name: string
    has_access: boolean
}

export interface LiteUser {
    id: number
    name: string
    email: string
    membership_code: string
    stage_id: number
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
