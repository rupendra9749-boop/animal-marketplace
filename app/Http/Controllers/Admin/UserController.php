<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserController extends Controller
{
    /** The roles an admin can give or take away. Seller covers selling, breeding, doctor and caretaker. */
    public const ROLES = ['is_seller', 'is_admin'];

    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->filled('role'), fn ($query) => match ($request->string('role')->toString()) {
                'admin' => $query->where('is_admin', true),
                'seller' => $query->where('is_seller', true),
                'breeder' => $query->where('is_breeder', true),
                'doctor' => $query->where('is_doctor', true),
                'caretaker' => $query->where('is_caretaker', true),
                'buyer' => $query->where('is_seller', false)->where('is_admin', false)->where('is_breeder', false)->where('is_doctor', false)->where('is_caretaker', false),
                default => $query,
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%'.$request->string('search').'%';
                $query->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('phone', 'like', $term)->orWhere('city', 'like', $term));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate(collect(self::ROLES)->push('is_active')->mapWithKeys(fn ($field) => [$field => ['nullable', 'boolean']])->all());

        $roles = collect(self::ROLES)->mapWithKeys(fn ($field) => [$field => $request->boolean($field)])->all();

        // Nobody can lock themselves out of the admin panel or suspend their own account.
        if ($user->id === Auth::id()) {
            $roles['is_admin'] = true;
            $active = true;
        } else {
            $active = $request->boolean('is_active');
        }

        $user->update([...$roles, 'is_active' => $active]);

        return back()->with('status', __(':name was updated.', ['name' => $user->name]));
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->id === Auth::id(), 403, __('You cannot delete your own account.'));

        $user->delete();

        return back()->with('status', __('User deleted.'));
    }
}
