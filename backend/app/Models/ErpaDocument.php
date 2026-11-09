<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ErpaDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'transaction_id',
        'document_type',
        'issue_date',
        'amount',
        'partner_name',
        'storage_path',
        'file_sha256',
        'statutory_retention_until',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'amount' => 'decimal:2',
            'statutory_retention_until' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
