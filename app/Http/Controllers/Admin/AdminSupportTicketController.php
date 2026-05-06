<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSupportTicketRequest;
use App\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminSupportTicketController extends Controller
{
    public function index(): View
    {
        $tickets = SupportTicket::query()
            ->with('user:id,name,email')
            ->latest()
            ->paginate(25);

        $statusLabels = SupportTicket::statusLabels();

        return view('admin.tickets.index', compact('tickets', 'statusLabels'));
    }

    public function show(SupportTicket $ticket): View
    {
        $ticket->load('user:id,name,email');
        $statusLabels = SupportTicket::statusLabels();

        return view('admin.tickets.show', compact('ticket', 'statusLabels'));
    }

    public function update(UpdateSupportTicketRequest $request, SupportTicket $ticket): RedirectResponse
    {
        $ticket->fill($request->validated());
        $ticket->save();

        return redirect()
            ->route('admin.tickets.show', $ticket)
            ->with('status', 'Ticket updated.');
    }
}
