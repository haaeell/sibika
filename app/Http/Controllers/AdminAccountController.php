<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAdminAccountRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminAccountController extends Controller
{
    public function edit(): View
    {
        return view('admin.account.edit');
    }

    public function update(UpdateAdminAccountRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $updates = ['email' => $data['email']];

        if (filled($data['password'] ?? null)) {
            $updates['password'] = $data['password'];
        }

        $request->user()->update($updates);

        return back()->with('success', 'Akun superadmin berhasil diperbarui.');
    }
}
