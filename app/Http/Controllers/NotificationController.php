<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener las notificaciones del usuario autenticado
        $notifications = Notification::where('user_id', Auth::user()->id)
            ->orderBy('sent_date', 'desc') // Ordenar por fecha de envío descendente
            ->paginate(10); // Paginación opcional

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('notifications.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar los datos de entrada
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|string|in:info,alert,warning',
        ]);

        // Obtener todos los usuarios
        $users = User::all();

        // Crear una notificación para cada usuario
        foreach ($users as $user) {
            Notification::create([
                'title' => $validated['title'],
                'message' => $validated['message'],
                'type' => $validated['type'],
                'sent_date' => now(), // Fecha de envío actual
                'user_id' => $user->id, // Asociar a cada usuario
            ]);
        }

        // Redireccionar con un mensaje de éxito
        return redirect()->route('notifications.create')->with('success', 'Notificación enviada a todos los usuarios.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', Auth::user()->id)
            ->firstOrFail();

        $notification->delete();

        return redirect()->route('notifications.index')->with('success', 'Notificación eliminada con éxito.');
    }
}
