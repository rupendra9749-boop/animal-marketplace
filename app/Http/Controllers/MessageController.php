<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $conversations = Conversation::with(['animal', 'buyer', 'seller', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->where('buyer_id', $user->id)
            ->orWhere('seller_id', $user->id)
            ->latest('updated_at')
            ->get();

        return view('messages.index', compact('conversations'));
    }

    public function start(Request $request, Animal $animal): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        abort_if($animal->user_id === Auth::id(), 400, __('You cannot message yourself about your own listing.'));

        $conversation = Conversation::firstOrCreate([
            'animal_id' => $animal->id,
            'buyer_id' => Auth::id(),
            'seller_id' => $animal->user_id,
        ]);

        $conversation->messages()->create([
            'sender_id' => Auth::id(),
            'body' => $validated['body'],
        ]);

        $conversation->touch();

        return redirect()->route('messages.show', $conversation)->with('status', __('Message sent.'));
    }

    public function startDirect(User $user): RedirectResponse
    {
        $me = Auth::user();

        abort_if($user->id === $me->id, 400, __('You cannot message yourself.'));
        abort_unless($me->isAdmin() || $user->isAdmin(), 403, __('Direct messages are only available with support/admin.'));

        return redirect()->route('messages.show', $this->conversationBetween($me, $user));
    }

    /** A buyer messages a doctor, caretaker, breeder or seller directly from their profile. */
    public function startWithProvider(User $user): RedirectResponse
    {
        $me = Auth::user();

        abort_if($user->id === $me->id, 400, __('You cannot message yourself.'));
        abort_unless($user->is_active && ($user->isDoctor() || $user->isCaretaker() || $user->isBreeder() || $user->isSeller()), 403, __('This person cannot be messaged directly.'));

        return redirect()->route('messages.show', $this->conversationBetween($me, $user));
    }

    private function conversationBetween(User $a, User $b): Conversation
    {
        [$first, $second] = $a->id < $b->id ? [$a, $b] : [$b, $a];

        return Conversation::firstOrCreate([
            'animal_id' => null,
            'buyer_id' => $first->id,
            'seller_id' => $second->id,
        ]);
    }

    public function contactSupport(): RedirectResponse
    {
        $admin = User::where('is_admin', true)->first();

        abort_unless($admin, 404, __('No support contact is configured yet.'));

        return $this->startDirect($admin);
    }

    public function show(Conversation $conversation): View
    {
        $this->authorizeParticipant($conversation);

        $conversation->load(['animal', 'buyer', 'seller', 'messages.sender']);

        $conversation->messages()
            ->where('sender_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('messages.show', compact('conversation'));
    }

    public function reply(Request $request, Conversation $conversation): RedirectResponse
    {
        $this->authorizeParticipant($conversation);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $conversation->messages()->create([
            'sender_id' => Auth::id(),
            'body' => $validated['body'],
        ]);

        $conversation->touch();

        return redirect()->route('messages.show', $conversation);
    }

    private function authorizeParticipant(Conversation $conversation): void
    {
        abort_unless(in_array(Auth::id(), [$conversation->buyer_id, $conversation->seller_id], true), 403);
    }
}
