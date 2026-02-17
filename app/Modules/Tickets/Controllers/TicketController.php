<?php

namespace App\Modules\Tickets\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Tickets\Services\TicketService;
use App\Modules\Tickets\Requests\CreateTicketRequest;
use App\Modules\Tickets\Resources\TicketResource;

class TicketController extends Controller
{
    public function __construct(private TicketService $service) {}

    public function index()
    {
        $tickets = Ticket::with(['client', 'status', 'assignedTo'])
            ->when(request('status'), fn($q, $status) => $q->whereHas('status', fn($sq) => $sq->where('ticket_status_name', $status)))
            ->when(request('assigned_to'), fn($q, $user) => $q->assignedTo($user))
            ->paginate(25);

        return view('tickets::index', compact('tickets'));
    }

    public function store(CreateTicketRequest $request)
    {
        $ticket = $this->service->create($request->validated());
        
        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Ticket created successfully');
    }

    public function assign(Ticket $ticket, int $userId)
    {
        $this->authorize('assign', $ticket);
        
        $this->service->assign($ticket, $userId);
        
        return back()->with('success', 'Ticket assigned');
    }
}
