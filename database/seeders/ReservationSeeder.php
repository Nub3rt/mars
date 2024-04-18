<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\ReservableItem;
use App\Models\Reservation;
use App\Models\User;

use Carbon\Carbon;


class ReservationSeeder extends Seeder
{
    // the number of washing machines to generate
   private const NUM_OF_WASHING_MACHINES = 2;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // first, create the two washing machines
        $washing_machines = [];
        for ($i = 1; $i <= ReservationSeeder::NUM_OF_WASHING_MACHINES; ++$i)
        {
            $washing_machines[] = ReservableItem::create([
                "name" => "mosómasina no. $i",
                "type" => "washing_machine",
                "default_reservation_duration" => 60,
                "is_default_compulsory" => true,
                "allowed_starting_minutes" => "0",
                "out_of_order_from" => null,
                "out_of_order_until" => null,
            ]);
        }

        // createing the rooms
        $rooms = ReservatbleItem::factory()->count(10)->create();

        foreach($washing_machines as $machine) {
            // for today and the next 14 days
            for ($day = 0; $day < 14; ++$day) {
                for ($hour = 0; $hour < 24; ++$hour) {
                    if (!random_int(0,2)) {
                        Reservation::create([
                            "reservable_item_id" => $machine->id,
                            "user_id" => User::all()->random()->id,
                            "verified" => true,
                            "reserved_from" => Carbon::today()->addDays($day)->addHours($hour),
                            "reserved_until" => Carbon::now()->addDays($day)->addHours($hour),
                        ]);
                    }
                }
            }
        }

        foreach ($rooms as $room) {
            $reservations = [];
            $reserved_from = Carbon::now()->addDays(random_int(0, 13))->addHours(random_int(0, 23))->addMinutes(random_int(0,59));
            for ($i = 0; $i < 50; ++$i) {
                Reservations::create([
                    'reservable_item_id' => $room->id,
                    'user_id' => User::all()->random()->id,
                    'verified' => random_int(0,1),
                    'reserved_from' => $reserved_from,
                    'reserved_until' => $reserved_from->addMinutes(random_int(1,180)),
                ]);
                
                $wasDeleted = false;
                foreach($reservations as $earlier) {
                    if ($earlier->conflictsWith($new_one)) {
                        $new_one->delete();
                        $wasDeleted = true;
                        break;
                    }
                }

                if (!$wasDeleted) $reservations[] = $new_one;
            }
        }


    }
}
