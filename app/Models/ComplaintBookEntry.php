<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintBookEntry extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'correlative',
        'type',
        'document_type',
        'document_number',
        'last_name',
        'first_name',
        'address',
        'phone',
        'email',
        'good_type',
        'good_description',
        'claimed_amount',
        'detail',
        'request',
        'status',
    ];

    /**
     * Generates the next correlative number for the given year, e.g. "0001-2026".
     */
    public static function nextCorrelative(): string
    {
        $year = now()->year;

        $count = static::whereYear('created_at', $year)->count() + 1;

        return sprintf('%04d-%d', $count, $year);
    }
}
