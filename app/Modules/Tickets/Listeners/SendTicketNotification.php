<?php

namespace App\Modules\Tickets\Listeners;

use App\Modules\Tickets\Events\TicketCreated;
use App\Modules\Tickets\Services\TicketNotificationService;

class SendTicketNotification
{
    public function __construct(
        private TicketNotificationService $notificationService
    ) {}

    public function handle(TicketCreated $event): void
    {
        $this->notificationService->notifyTicketCreated($event->ticket);
    }
}
