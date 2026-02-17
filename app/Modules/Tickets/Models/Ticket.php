<?php

namespace App\Modules\Tickets\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ticket_prefix', 'ticket_number', 'ticket_subject', 
        'ticket_details', 'ticket_priority', 'ticket_status_id',
        'ticket_client_id', 'ticket_contact_id', 'ticket_assigned_to',
        'ticket_billable', 'ticket_created_by'
    ];

    protected $casts = [
        'ticket_billable' => 'boolean',
        'ticket_created_at' => 'datetime',
        'ticket_closed_at' => 'datetime'
    ];

    // Relationships
    public function client() {
        return $this->belongsTo(Client::class, 'ticket_client_id');
    }

    public function status() {
        return $this->belongsTo(TicketStatus::class, 'ticket_status_id');
    }

    public function assignedTo() {
        return $this->belongsTo(User::class, 'ticket_assigned_to');
    }

    public function replies() {
        return $this->hasMany(TicketReply::class);
    }

    public function attachments() {
        return $this->hasMany(TicketAttachment::class);
    }

    // Scopes
    public function scopeOpen($query) {
        return $query->whereHas('status', fn($q) => $q->where('ticket_status_name', '!=', 'Closed'));
    }

    public function scopeAssignedTo($query, $userId) {
        return $query->where('ticket_assigned_to', $userId);
    }

    // Accessors
    public function getFullNumberAttribute() {
        return $this->ticket_prefix . $this->ticket_number;
    }
}
