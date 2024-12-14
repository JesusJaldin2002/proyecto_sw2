@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="text-center">Mis Notificaciones</h1>

    @if(session('success'))
        <div class="alert alert-success mt-3">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-responsive mt-4">
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Título</th>
                    <th>Mensaje</th>
                    <th>Tipo</th>
                    <th>Fecha Enviada</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notifications as $notification)
                    <tr>
                        <td>{{ $notification->title }}</td>
                        <td>{{ $notification->message }}</td>
                        <td>
                            @if($notification->type === 'info')
                                <span class="badge bg-primary text-white">Información</span>
                            @elseif($notification->type === 'alert')
                                <span class="badge bg-danger text-white">Alerta</span>
                            @elseif($notification->type === 'warning')
                                <span class="badge bg-warning text-dark">Advertencia</span>
                            @else
                                <span class="badge bg-secondary text-white">Otro</span>
                            @endif
                        </td>
                        <td>{{ $notification->sent_date->format('d/m/Y H:i') }}</td>
                        <td class="text-center">
                            <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta notificación?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No tienes notificaciones.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    @if($notifications->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
