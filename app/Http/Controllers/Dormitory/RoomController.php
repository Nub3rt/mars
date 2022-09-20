<?php

namespace App\Http\Controllers\Dormitory;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use App\Models\Room;
use App\Models\User;

class RoomController extends Controller
{
    /**
     * Returns the room assignment page
     */
    public function index()
    {
        $this->authorize('viewAny', Room::class);
        $users=User::active()->resident()->get();

        $rooms = Room::with('users')->get();
        return view('dormitory.rooms.app', ['users' => $users, 'rooms' => $rooms]);
    }
    /**
     * Updates the capacity of the room.
     * Returns an error if the new capacity is out of bounds.
     */
    public function updateRoomCapacity(Room $room, Request $request)
    {
        $this->authorize('updateAny', Room::class);

        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:add,remove'
        ]);
        $validator->validate();
        $new_capacity=$room->capacity+($request->type=='add' ? 1 : -1);
        if ($new_capacity<$room->residentNumber()) {
            return back()->with('error', __('rooms.no_capacity_error'));
        }
        if ($new_capacity>4 || $new_capacity<1) {
            return back()->with('error', __('rooms.capacity_bounds_error'));
        }
        if ($request->type=='add') {
            $room->increment('capacity');
        } else {
            $room->decrement('capacity');
        }

        return back();
    }
    /**
     * Updates the residents of all rooms.
     * First it sets all users' rooms to null and sets the correct values after.
     */
    public function updateResidents(Request $request)
    {
        $this->authorize('updateAny', Room::class);

        $rooms=Room::all();
        $users=User::all();
        foreach ($users as $user) {
            $user->update(['room' => null]);
        }
        foreach ($rooms as $room) {
            $userIds=isset($request->rooms[$room->name]) ? $request->rooms[$room->name] : null;
            if ($userIds!==null) {
                foreach ($userIds as $userId) {
                    // $userId can be 'null' if one of the users has been taken out of the assignment.
                    if ($userId!='null') {
                        $user=User::find($userId);
                        if ($user!=null) {
                            $user->update(['room' => $room->name]);
                        }
                    }
                }
            }
        }
        return back();
    }
}