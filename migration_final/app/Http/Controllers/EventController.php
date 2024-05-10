<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EventController extends Controller
{
    public function create(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'event_date' => 'required|date',
        'description' => 'nullable|string',
    ]);

    Event::create([
        'store_id' => auth()->id(), // Assuming the store's ID is the user ID
        'name' => $request->name,
        'event_date' => $request->event_date,
        'description' => $request->description,
    ]);

    return redirect()->route('events.index')->with('success', 'Event created successfully.');
}

}
