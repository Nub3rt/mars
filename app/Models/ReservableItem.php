<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservableItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'default_reservation_duration',
        'is_default_compulsory',
        'allowed_starting_minutes',
        'out_of_order_from',
        'out_of_order_until',
    ];

    /**
     * @return HasMany The reservations that have been made for this particular item
     */
    public function reservations() : HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * @return BelongsToMany The users that have made a reservation for this item
     */
    public function users() : BelongsToMany
    {
        return $this->belongsToMany(User::Class, Reservation::class, 'reservable_item_id', 'user_id');
    }
}
