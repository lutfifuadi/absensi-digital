<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'action', 'module', 'description',
        'ip_address', 'user_agent', 'old_data', 'new_data',
    ];

    protected $casts = [
        'old_data'   => 'array',
        'new_data'   => 'array',
        'created_at' => 'datetime',
    ];

    /** Relasi ke user. */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper static untuk mencatat aktivitas.
     */
    public static function record(
        string $action,
        string $module,
        string $description,
        ?array $oldData = null,
        ?array $newData = null
    ): void {
        /** @var \Illuminate\Http\Request $request */
        $request = app('request');

        static::create([
            'user_id'     => auth()->id(),
            'action'      => $action,
            'module'      => $module,
            'description' => $description,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'old_data'    => $oldData,
            'new_data'    => $newData,
        ]);
    }
}
