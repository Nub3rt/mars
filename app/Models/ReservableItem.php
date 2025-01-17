<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use app\Models\Reservation;

use Carbon\Carbon;

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

    /**
     * @return bool Returns whether the item is currently out of order.
     */
    public function isOutOfOrder() : bool
    {
        if (is_null($this->out_of_order_from))
            return false;

        $from = new Carbon($this->out_of_order_from);
        $now = Carbon::now();
        if ($now < $from)
            return false;
        
        if (is_null($this->out_of_order_until))
            return true;

        $until = new Carbon($this->out_of_order_until);
        return $now < $until;
    }

    /**
     * @return bool Returns whether the item is free at the moment; or false if out of order.
     */
    public function isFree() : bool
    {
        if ($this->isOutOfOrder())
            return false;

        $now = Carbon::now();
        return Reservation::where('reservable_item_id', $this->id)
                   ->where('reserved_from', '<=', $now)
                   ->where('reserved_until', '>', $now)
                   ->doesntExists();
    }
}
