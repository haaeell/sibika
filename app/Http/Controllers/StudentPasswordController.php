<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateStudentPasswordRequest;
use Illuminate\Http\RedirectResponse;

class StudentPasswordController extends Controller
{
    public function update(UpdateStudentPasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->validated()['password'],
            'must_change_password' => false,
        ]);

        return redirect()->route('siswa.dashboard')->with('success', 'Password berhasil diganti');
    }
}
