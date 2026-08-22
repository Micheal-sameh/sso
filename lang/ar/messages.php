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
    'invalid_credentials' => 'بيانات الدخول غير صحيحة.',
    'account_inactive' => 'الحساب غير مفعّل.',
    'invalid_or_expired_verification_session' => 'جلسة التحقق غير صالحة أو منتهية الصلاحية.',
    'logged_out' => 'تم تسجيل الخروج.',
    'logged_out_flash' => 'لقد قمت بتسجيل الخروج.',
    'welcome_back' => 'مرحبًا بعودتك، :name!',

    // MFA
    'invalid_mfa_code' => 'رمز التحقق غير صحيح.',
    'mfa_not_enabled' => 'التحقق بخطوتين غير مفعّل لهذا الحساب.',
    'mfa_setup_session_expired' => 'انتهت صلاحية جلسة الإعداد هذه. يرجى بدء إعداد التحقق بخطوتين من جديد.',
    'mfa_enabled' => 'تم تفعيل التحقق بخطوتين بنجاح.',
    'mfa_disabled' => 'تم إلغاء تفعيل التحقق بخطوتين.',
    'mfa_verified' => 'تم التحقق بنجاح.',
    'mfa_reset_for_user' => 'تمت إعادة تعيين التحقق بخطوتين لـ :name. سيحتاجون إلى إعداده مرة أخرى.',

    // Password
    'password_updated' => 'تم تحديث كلمة المرور.',
    'password_reset_success' => 'تمت إعادة تعيين كلمة المرور الخاصة بك. يرجى تسجيل الدخول.',
    'password_reset_email_sent' => 'تم إرسال بريد إعادة تعيين كلمة المرور.',

    // Users / roles
    'user_not_found' => 'لم يتم العثور على مستخدم مطابق.',
    'cannot_change_own_admin_access' => 'لا يمكنك تغيير صلاحيات الإدارة الخاصة بك.',
    'cannot_change_own_role' => 'لا يمكنك تغيير دورك الخاص.',
    'unauthorized_role_change' => 'يمكن فقط للمشرف العام تعيين أو تغيير دوري المشرف والمشرف العام.',
    'user_created' => 'تم إنشاء المستخدم بنجاح.',
    'user_updated' => 'تم تحديث المستخدم بنجاح.',
    'user_status_updated' => 'تم تحديث حالة المستخدم.',
    'user_data_update_requirement_updated' => 'تم تحديث متطلب تحديث بيانات المستخدم.',
    'admin_access_updated' => 'تم تحديث صلاحيات الإدارة.',
    'access_granted' => 'تم منح الوصول.',
    'access_revoked' => 'تم إلغاء الوصول.',
    'added_to_group' => 'تمت الإضافة إلى المجموعة.',
    'removed_from_group' => 'تمت الإزالة من المجموعة.',

    // OAuth clients / apps
    'invalid_client_credentials' => 'بيانات اعتماد العميل غير صحيحة.',
    'oauth_authorization_expired' => 'انتهت صلاحية طلب التفويض هذا أو تم استخدامه بالفعل. يرجى محاولة تسجيل الدخول مرة أخرى من التطبيق.',
    'client_registered' => 'تم تسجيل العميل بنجاح.',
    'client_registered_with_secret' => 'تم تسجيل العميل. انسخ المفتاح السري الآن — لن يتم عرضه مرة أخرى.',
    'client_updated' => 'تم تحديث العميل بنجاح.',
    'client_revoked' => 'تم إلغاء العميل.',
    'app_access_revoked' => 'تم إلغاء وصول التطبيق.',
    'token_revoked' => 'تم إلغاء الرمز.',

    // Stages
    'stage_in_use' => 'لا يزال هناك مستخدمون مرتبطون بهذه المرحلة، ولا يمكن حذفها.',
    'stage_created' => 'تم إنشاء المرحلة بنجاح.',
    'stage_updated' => 'تم تحديث المرحلة بنجاح.',
    'stage_deleted' => 'تم حذف المرحلة.',

    // Groups
    'group_created' => 'تم إنشاء المجموعة بنجاح.',
    'group_updated' => 'تم تحديث المجموعة بنجاح.',
    'group_deleted' => 'تم حذف المجموعة.',

    // CMC posts
    'post_being_published' => 'جارٍ نشر المنشور.',
    'post_being_updated' => 'جارٍ تحديث المنشور.',
    'post_deleted' => 'تم حذف المنشور.',

    // Notifications
    'notification_being_sent' => 'جارٍ إرسال الإشعار.',
    'notification_deleted' => 'تم حذف الإشعار.',
    'notification_marked_read' => 'تم تعليم الإشعار كمقروء.',
    'all_notifications_marked_read' => 'تم تعليم جميع الإشعارات كمقروءة.',
    'notifications_marked_read' => 'تم تعليم الإشعارات كمقروءة.',

    // Infrastructure
    'database_error' => 'يتعذر علينا إتمام طلبك في الوقت الحالي. يرجى المحاولة مرة أخرى بعد بضع دقائق.',

];
