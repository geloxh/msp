<?php

namespace App\Modules\Tickets\Repositories;

use App\Modules\Tickets\Models\Ticket;

class TicketRepository
{
    public function create(array $data): Ticket
    {
        return Ticket::create($data);
    }

    public function findWithRelations(int $id): ?Ticket
    {
        return Ticket::with(['client', 'status', 'replies', 'attachments'])
            ->findOrFail($id);
    }

    public function getOpenTicketsForClient(int $clientId)
    {
        return Ticket::where('ticket_client_id', $clientId)
            ->open()
            ->get();
    }
}
