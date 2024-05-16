<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

use Carbon\Carbon;

use App\Models\ReservableItem;
use App\Models\Reservation;
use App\Mail\ReservationVerified;

class ReservationController extends Controller
{
    /**
     * Lists reservations for a given item.
     */
    public function index(ReservableItem $item)
    {
        $this->authorize('viewAny', ReservableItem::class);

        return response()->json(
            Reservation::where('reservable_item_id', $item->id)
                         ->orderBy('reserved_from')
                         ->get()
        );
    }

    /**
     * Lists reservations for all washing machines.
     */
    public function indexForWashingMachines()
    {
        $this->authorize('viewAny', ReservableItem::class);

        $items = ReservableItem::where('type', 'washing_machine')->get();
        $from = Carbon::today()->startOfWeek();
        $until = $from->copy()->addDays(7);
        return view('reservations.index_for_washing_machines', [
            'items' => $items->all(),
            'from' => $from,
            'until' => $until,
            'blocks' => $items->map(function (ReservableItem $item) use ($from, $until) {
                return ReservableItemController::listOfBlocks($item, $from, $until);
            })
        ]);
    }

    /**
     * Lists the details of a reservation.
     */
    public function show(Reservation $reservation)
    {
        $this->authorize('view', $reservation);

        return view('reservations.show', [
            'reservation' => $reservation
        ]);
    }

    /**
     * Returns a form for creating a reservation.
     */
    public function create(ReservableItem $item)
    {
        $this->authorize('requestReservation', $item);

        return view('reservations.edit', [
            'item' => $item
        ]);
    }

    /**
     * Aborts the request if there is already a reservation
     * which would conflict with the one given.
     * Note: we assume that this reservation is not yet saved
     */
    public static function abortConflictingReservation(Reservation $newReservation)
    {
        $conflictingReservations = $newReservation->reservableItem
            ->reservationsInSlot($newReservation->reserved_from, $newReservation->reserved_until);

        if (!$conflictingReservations->empty()) {
            abort(409, "Reservation already exists in the given interval:
                {$conflictingReservations->first()->reserved_from},
                {$conflictingReservations->first()->reserved_until}");
        }
    }

    /**
     * The common validation process
     * of store and update.
     */
    public static function validateReservation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:2047',
            'reserved_from' => 'required|date',
            'reserved_until' => 'required|date'
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->reserved_from > $request->reserved_until) {
                $validator->errors()->add('reserved_until',
                    'The reservation cannot end before it starts.');
            }
        });

        return $validator->validate();
    }

    /**
     * Stores a reservation based on
     * a ReservableItem provided separately
     * and the data in the request.
     */
    public function store(ReservableItem $item, Request $request)
    {
        $this->authorize('requestReservation', $item);

        $validatedData = self::validateReservation($request);

        // we do not save it yet!
        $newReservation = new Reservation();
        $newReservation->reservable_item_id = $item->id;
        $newReservation->user_id = user()->id;
        $newReservation->title = $validatedData['title'];
        $newReservation->note = $validatedData['note'];
        $newReservation->reserved_from = Carbon::make($validatedData['reserved_from']);
        $newReservation->reserved_until = Carbon::make($validatedData['reserved_until']);

        $newReservation->verified = Auth::user()->can('reserveImmediately', $item);

        ReservationController::abortConflictingReservation($newReservation);

        // and finally:
        $newReservation->save();

        return redirect()->route('reservations.show', $newReservation);
    }

    /**
     * Returns a form for creating a reservation.
     */
    public function edit(Reservation $reservation)
    {
        $this->authorize('modify', $reservation);

        return view('reservations.edit', [
            'reservation' => $reservation
        ]);
    }

    /**
     * Updates a reservation with an edited version.
     */
    public function update(Reservation $reservation, Request $request)
    {
        $this->authorize('modify', $reservation);

        $validatedData = self::validateReservation($request);

        // we do not save it yet!
        $reservation->title = $validatedData['title'];
        $reservation->note = $validatedData['note'];
        $reservation->reserved_from = Carbon::make($validatedData['reserved_from']);
        $reservation->reserved_until = Carbon::make($validatedData['reserved_until']);

        $reservation->verified = Auth::user()->can('reserveImmediately', $reservation->item);

        ReservationController::abortConflictingReservation($reservation);

        // and finally:
        $reservation->save();

        return redirect()->route('reservations.show', $reservation);
    }

    /**
     * Enables a user with administrative rights to approve a reservation.
     */
    public function verify(Reservation $reservation) {
        $this->authorize('administer', Reservation::class);
        if ($reservation->verified) {
            abort(400); // TODO: check this out
        } else {
            $reservation->verified = true;
            $reservation->save();

            Mail::to($reservation->user)->queue(new ReservationVerified(
                user()->name,
                $reservation
            ));

            return redirect()->route('reservations.show', $reservation);
        }
    }

    /**
     * Deletes a reservation.
     */
    public function delete(Reservation $reservation) {
        $this->authorize('modify', $reservation);

        $reservation->delete();
        return redirect()->route('reservations.items.show', $reservation->reservableItem);
    }
}
