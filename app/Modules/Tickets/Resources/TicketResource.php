<?php

namespace App\Modules\Tickets\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->ticket_id,
            'number' => $this->full_number,
            'subject' => $this->ticket_subject,
            'priority' => $this->ticket_priority,
            'status' => $this->status->ticket_status_name,
            'client' => [
                'id' => $this->client->client_id,
                'name' => $this->client->client_name
            ],
            'assigned_to' => $this->assignedTo?->user_name,
            'created_at' => $this->ticket_created_at->toIso8601String(),
            'links' => [
                'self' => route('api.tickets.show', $this->ticket_id)
            ]
        ];
    }
}
