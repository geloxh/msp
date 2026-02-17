<?php

namespace App\Modules\Tickets\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTicketRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'ticket_subject' => 'required|string|max:255',
            'ticket_details' => 'required|string',
            'ticket_priority' => 'required|in:Low,Medium,High,Critical',
            'ticket_client_id' => 'required|exists:clients,client_id',
            'ticket_contact_id' => 'nullable|exists:contacts,contact_id',
            'ticket_assigned_to' => 'nullable|exists:users,user_id',
            'attachments.*' => 'file|max:10240|mimes:pdf,jpg,png,doc,docx'
        ];
    }
}
