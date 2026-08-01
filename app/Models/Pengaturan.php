<?php

namespace App\Models;

use App\Observers\PengaturanObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([PengaturanObserver::class])]
class Pengaturan extends Model
{
    use HasFactory;

    protected $table = 'pengaturan';

    protected $fillable = [
        'tenant_id',
        'key',
        'value',
        'group',
    ];
}
