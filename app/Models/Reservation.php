<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservable_item_id',
        'user_id',
        'title',
        'note',
        'reserved_from',
        'reserved_until',
    ];

    /**
     * @return BelongsTo The item reserved
     */
    public function reservableItem() : BelongsTo
    {
        return $this->belongsTo(ReservableItem::class);
    }

    /**
     * @return BelongsTo The user that made the reservation
     */
    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return bool True if the reservation conflicts with the given one
     */
    public function conflictsWith(Reservation $that) : bool
    {
        if ($this == $that || $this->reservable_item_id != $that->reservable_item_id)
            return false;

        return $this->reserved_from <= $that->reserved_from && $this->reserved_until > $that->reserved_from ||
            $that->reserved_from <= $this->reserved_from && $that->reserved_until > $this->reserved_from;
    }
}
