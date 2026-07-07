<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait HasPhotoAutoCleanup
{
    public static function bootHasPhotoAutoCleanup()
    {
        static::updating(function ($model) {
            $field = $model->photoField ?? 'foto';
            if ($model->isDirty($field)) {
                $oldValue = $model->getOriginal($field);
                if ($oldValue && Storage::disk('public')->exists($oldValue)) {
                    Storage::disk('public')->delete($oldValue);
                }
            }
        });

        static::deleting(function ($model) {
            $field = $model->photoField ?? 'foto';
            $value = $model->{$field};
            if ($value && Storage::disk('public')->exists($value)) {
                Storage::disk('public')->delete($value);
            }
        });
    }
}
