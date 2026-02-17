<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id('ticket_id');
            $table->string('ticket_prefix', 10);
            $table->integer('ticket_number');
            $table->string('ticket_subject');
            $table->text('ticket_details');
            $table->enum('ticket_priority', ['Low', 'Medium', 'High', 'Critical']);
            $table->foreignId('ticket_status_id')->constrained('ticket_statuses');
            $table->foreignId('ticket_client_id')->constrained('clients');
            $table->foreignId('ticket_contact_id')->nullable()->constrained('contacts');
            $table->foreignId('ticket_assigned_to')->nullable()->constrained('users');
            $table->boolean('ticket_billable')->default(true);
            $table->foreignId('ticket_created_by')->constrained('users');
            $table->timestamp('ticket_closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['ticket_client_id', 'ticket_status_id']);
            $table->unique(['ticket_prefix', 'ticket_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
