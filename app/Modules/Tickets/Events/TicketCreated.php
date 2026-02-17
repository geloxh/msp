<?php

namespace App\Modules\Tickets\Events;

use App\Modules\Tickets\Models\Ticket;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Ticket $ticket) {}
}
