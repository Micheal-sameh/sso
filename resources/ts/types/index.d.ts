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

export interface GroupMember {
    id: number
    name: string
    email: string
    membership_code: string
}

export interface GroupMemberOption extends GroupMember {
    is_member: boolean
}

export interface Group {
    id: number
    name: string
    description: string | null
    users_count?: number
    users?: GroupMember[]
}

export interface UserGroupSummary {
    id: number
    name: string
}

export interface UserGroupOption {
    id: number
    name: string
    is_member: boolean
}

export interface User {
    id: number
    uuid: string
    membership_code: string
    name: string
    email: string
    phone: string | null
    date_of_birth: string | null
    avatar: string | null
    role: 'superadmin' | 'admin' | 'secretary' | 'user'
    is_active: boolean
    requires_data_update: boolean
    wants_donation_reminders: boolean
    is_admin: boolean
    mfa_enabled: boolean
    mfa_enabled_at: string | null
    mfa_recovery_codes_remaining: number | null
    mfa_recovery_codes_total: number
    can_manage_admin_access: boolean
    can_manage_cmc_posts: boolean
    can_manage_donations: boolean
    stage: UserStageSummary
    email_verified_at: string | null
    created_at: string | null
    updated_at: string | null
    groups: UserGroupSummary[]
}

export interface UserMfa {
    id: number
    is_enabled: boolean
}

export interface OAuthClient {
    id: string
    name: string
    redirect_uris: string[]
    personal_access_client: boolean
    password_client: boolean
    grant_types: string[]
    revoked: boolean
}

export interface ClientMeta {
    id: number
    oauth_client_id: string
    app_name: string
    app_logo_url: string | null
    homepage_url: string | null
    logout_uri: string | null
    is_internal: boolean
    is_verified: boolean
    access_mode: 'all' | 'stage' | 'specific' | 'group'
    oauth_client: OAuthClient
    stages?: Stage[]
    allowed_users?: { id: number }[]
    groups?: Group[]
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

export interface NotificationCreator {
    id: number
    name: string
}

export type NotificationType = 'manual' | 'cmc' | 'youtube' | 'donation_reminder' | 'birthday'

export interface AppNotification {
    id: number
    message: string
    image_url: string | null
    target_type: 'all' | 'stage' | 'specific' | 'group'
    type: NotificationType
    creator: NotificationCreator | null
    stages?: Stage[]
    groups?: Group[]
    target_users?: { id: number; name: string }[]
    created_at: string
}

export interface UserNotification {
    id: number
    message: string
    image_url: string | null
    is_read: boolean
    created_at: string
    cmc_post_id: number | null
    type: NotificationType
}

export interface CmcPostCreator {
    id: number
    name: string
}

export interface CmcPost {
    id: number
    text: string | null
    image_url: string | null
    url: string | null
    url_title: string | null
    url_description: string | null
    url_image: string | null
    creator: CmcPostCreator | null
    created_at: string
    is_pinned: boolean
}

export interface YoutubeVideo {
    id: number
    video_id: string
    title: string
    thumbnail_url: string | null
    published_at: string | null
    is_pinned: boolean
    watch_url: string
}

export interface CopticCommemoration {
    name: string
    url: string | null
}

export interface CopticReading {
    title: string | null
    reference: string | null
    text: string | null
}

export interface CopticReadingSubSection {
    title: string | null
    readings: CopticReading[]
}

export interface CopticReadingSection {
    title: string | null
    subSections: CopticReadingSubSection[]
}

export interface CopticCalendar {
    date: string | null
    commemorations: CopticCommemoration[]
    readings: CopticReadingSection[]
}

export interface Fund {
    id: number
    name: string
    description: string | null
    is_active: boolean
    donations_count?: number
}

export type DonationGateway = 'nbe' | 'cib'
export type DonationStatus = 'pending' | 'paid' | 'failed' | 'cancelled' | 'refunded'

export interface Donation {
    id: number
    uuid: string
    amount: number
    currency: string
    gateway: DonationGateway
    status: DonationStatus
    is_recurring: boolean
    fund: { id: number; name: string } | null
    donor: { id: number; name: string } | null
    paid_at: string | null
    created_at: string
}

export interface DonationTotals {
    total_paid: number
    this_month: number
    this_year: number
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
    unread_notifications_count: number
    recent_notifications: UserNotification[]
    locale: string
    direction: 'ltr' | 'rtl'
}
