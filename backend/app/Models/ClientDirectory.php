<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientDirectory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        't_number',
        'postal_code',
        'address',
        'phone',
        'email',
        'contact_person',
        'notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
