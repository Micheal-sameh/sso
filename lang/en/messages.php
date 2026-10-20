<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used throughout the application for
    | user-facing messages that originate on the backend: exception messages,
    | flash messages, and API response messages.
    |
    */

    // Auth / credentials
    'invalid_credentials' => 'Invalid credentials.',
    'account_inactive' => 'Account is inactive.',
    'dashboard_access_denied' => 'Your account does not have access to this dashboard.',
    'invalid_or_expired_verification_session' => 'Invalid or expired verification session.',
    'logged_out' => 'Logged out.',
    'logged_out_flash' => 'You have been logged out.',
    'welcome_back' => 'Welcome back, :name!',

    // MFA
    'invalid_mfa_code' => 'The verification code is invalid.',
    'mfa_not_enabled' => 'Two-factor authentication is not enabled for this account.',
    'mfa_setup_session_expired' => 'This setup session has expired. Start MFA setup again.',
    'mfa_enabled' => '2FA enabled successfully.',
    'mfa_disabled' => '2FA has been disabled.',
    'mfa_verified' => 'Verified successfully.',
    'mfa_reset_for_user' => "2FA reset for :name. They'll need to set it up again.",

    // Password
    'password_updated' => 'Password updated.',
    'password_reset_success' => 'Your password has been reset. Please sign in.',
    'password_reset_email_sent' => 'Password reset email sent.',
    'password_reset_if_exists' => 'If an account with that email exists, a password reset link has been sent.',
    'reset_link_invalid' => 'This reset link has expired or was already used. Ask an administrator for a new one.',

    // Users / roles
    'user_not_found' => 'No matching user found.',
    'cannot_change_own_admin_access' => 'You cannot change your own admin access.',
    'cannot_change_own_role' => 'You cannot change your own role.',
    'unauthorized_account_action' => 'Only a super admin can perform this action on an admin or super admin account.',
    'unauthorized_role_change' => 'Only a super admin can assign or change the Admin and Super Admin roles.',
    'user_created' => 'User created successfully.',
    'user_updated' => 'User updated successfully.',
    'user_status_updated' => 'User status updated.',
    'user_data_update_requirement_updated' => 'User data-update requirement updated.',
    'admin_access_updated' => 'Admin access updated.',
    'access_granted' => 'Access granted.',
    'access_revoked' => 'Access revoked.',
    'added_to_group' => 'Added to group.',
    'removed_from_group' => 'Removed from group.',
    'avatar_removed' => 'Profile picture removed.',

    // OAuth clients / apps
    'invalid_client_credentials' => 'Invalid client credentials.',
    'oauth_authorization_expired' => 'That authorization request has expired or was already used. Please try signing in again from the app.',
    'client_registered' => 'Client registered successfully.',
    'client_registered_with_secret' => 'Client registered. Copy the secret now — it will not be shown again.',
    'client_updated' => 'Client updated successfully.',
    'client_revoked' => 'Client revoked.',
    'app_access_revoked' => 'App access revoked.',
    'token_revoked' => 'Token revoked.',

    // Stages
    'stage_in_use' => 'This stage still has users assigned to it and cannot be deleted.',
    'stage_created' => 'Stage created successfully.',
    'stage_updated' => 'Stage updated successfully.',
    'stage_deleted' => 'Stage deleted.',

    // Groups
    'group_created' => 'Group created successfully.',
    'group_updated' => 'Group updated successfully.',
    'group_deleted' => 'Group deleted.',

    // Families
    'family_created' => 'Family created. Add members below.',
    'family_deleted' => 'Family deleted.',
    'family_member_added' => 'Family member added.',
    'family_member_removed' => 'Family member removed.',
    'family_require_data_update_all' => 'Marked :count user(s) across all families as requiring a data update.',
    'family_require_data_update_one' => 'Marked :count member(s) of this family as requiring a data update.',
    'family_role_taken' => 'This family already has someone assigned to that role.',
    'family_no_members' => 'Empty family',

    // CMC posts
    'post_being_published' => 'Post is being published.',
    'post_being_updated' => 'Post is being updated.',
    'post_deleted' => 'Post deleted.',

    // YouTube videos
    'videos_synced' => 'Synced :count video(s) from the channel.',

    // Notifications
    'notification_being_sent' => 'Notification is being sent.',
    'notification_deleted' => 'Notification deleted.',
    'notification_marked_read' => 'Notification marked as read.',
    'all_notifications_marked_read' => 'All notifications marked as read.',
    'notifications_marked_read' => 'Notifications marked as read.',

    // Spiritual notes
    'spiritual_note_saved' => 'Saved.',

    // Infrastructure
    'database_error' => "We're unable to complete your request right now. Please try again in a few minutes.",

];
