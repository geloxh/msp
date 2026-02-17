<?php

namespace App\Modules\Tickets\Services;

use App\Modules\Tickets\Models\Ticket;
use App\Modules\Tickets\Events\TicketCreated;
use App\Modules\Tickets\Repositories\TicketRepository;

class TicketService
{
    public function __construct(
        private TicketRepository $repository,
        private TicketNotificationService $notificationService
    ) {}

    public function create(array $data): Ticket
    {
        $ticket = $this->repository->create([
            ...$data,
            'ticket_number' => $this->generateTicketNumber(),
            'ticket_prefix' => config('tickets.prefix', 'TKT-')
        ]);

        event(new TicketCreated($ticket));
        
        return $ticket;
    }

    public function assign(Ticket $ticket, int $userId): Ticket
    {
        $ticket->update(['ticket_assigned_to' => $userId]);
        $this->notificationService->notifyAssignment($ticket, $userId);
        
        return $ticket->fresh();
    }

    public function close(Ticket $ticket, string $resolution = null): Ticket
    {
        $closedStatus = TicketStatus::where('ticket_status_name', 'Closed')->first();
        
        $ticket->update([
            'ticket_status_id' => $closedStatus->id,
            'ticket_closed_at' => now(),
            'ticket_resolution' => $resolution
        ]);

        return $ticket;
    }

    private function generateTicketNumber(): int
    {
        return Ticket::max('ticket_number') + 1;
    }
}
