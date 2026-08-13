<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait HasAuditLog
{
    public static function bootHasAuditLog(): void
    {
        static::created(function (Model $model) {
            static::writeAuditLog($model, 'created', null, $model->getAttributes());
        });

        static::updated(function (Model $model) {
            static::writeAuditLog($model, 'updated', $model->getOriginal(), $model->getChanges());
        });

        static::deleted(function (Model $model) {
            static::writeAuditLog($model, 'deleted', $model->getAttributes(), null);
        });
    }

    protected static function writeAuditLog(Model $model, string $action, ?array $oldValues, ?array $newValues): void
    {
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'model_type' => static::class,
                'model_id' => $model->getKey(),
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
            ]);
        } catch (\Throwable) {
            // Never let audit logging break the main operation.
        }
    }
}
