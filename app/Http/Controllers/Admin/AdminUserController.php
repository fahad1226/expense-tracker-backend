<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use App\Models\User;
use App\Support\SupportedCurrencies;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function edit(User $user): View
    {
        $currencies = SupportedCurrencies::options();

        return view('admin.users.edit', compact('user', 'currencies'));
    }

    public function update(UpdateAdminUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['is_admin'] = $request->boolean('is_admin');

        $user->fill($data);
        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('admin.users.index')
                ->withErrors(['delete' => 'You cannot delete your own account.']);
        }

        if ($user->isAdmin()) {
            $others = User::query()->where('is_admin', true)->where('id', '!=', $user->id)->exists();
            if (! $others) {
                return redirect()
                    ->route('admin.users.index')
                    ->withErrors(['delete' => 'Cannot delete the last administrator.']);
            }
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User deleted.');
    }
}
