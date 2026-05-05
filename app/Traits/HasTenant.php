<?php

namespace App\Traits;

use App\Scopes\TenantScope;

trait HasTenant
{
    /**
     * Boot the trait.
     */
    protected static function bootHasTenant()
    {
        /*
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if (!$model->school_id && app()->has('current_school')) {
                $model->school_id = app('current_school')->id;
            }
        });
        */
    }

    /**
     * Get the school that owns the model.
     */
    public function school()
    {
        return $this->belongsTo(\App\Models\School::class, 'school_id');
    }
}
