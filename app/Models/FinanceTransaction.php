<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class FinanceTransaction extends Model
{
    use HasFactory, LogsActivity;

    public const EXPENSE_CATEGORIES = ['Packing', 'Ongkir', 'Admin Marketplace', 'Iklan', 'Lain-lain'];

    protected $fillable = [
        'type', 'category', 'amount', 'transaction_date', 'description',
        'reference_type', 'reference_id', 'user_id',
    ];

    protected $casts = [
        'amount' => 'integer',
        'transaction_date' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['type', 'category', 'amount'])
            ->logOnlyDirty()
            ->useLogName('keuangan');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }
}
