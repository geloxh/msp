<?php
namespace App\Modules\Clients\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Clients\Models\Client;
use App\Modules\Clients\Services\ClientService;
use App\Modules\Clients\Requests\CreateClientRequest;

class ClientController extends Controller {
    public function __construct(private ClientService $service) {}

    public function index() {
        $clients = Client::active()->orderBy('client_name')->paginate(25);
        return view('clients::index', compact('clients'));
    }

    public function show(Client $client) {
        $client->load(['contacts','locations','tickets','invoices']);
        return view('clients::show', compact('client'));
    }

    public function store(CreateClientRequest $request) {
        $client = $this->service->create($request->validated());
        return redirect()->route('clients.show', $client)->with('success', 'Client created.');
    }

    public function update(CreateClientRequest $request, Client $client) {
        $this->service->update($client, $request->validated());
        return back()->with('success', 'Client updated.');
    }

    public function destroy(Client $client) {
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Client archived.');
    }
}