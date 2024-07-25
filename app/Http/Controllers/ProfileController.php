<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $data = [
            'pageTitle' => 'Informasi Profile',
            'user' => $request->user(),
        ];

        return view('profile.edit', $data);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        if ($request->hasFile('image')) {
            // delete old image
            if ($request->user()->image) {
                $imagePath = public_path('assets/profile/') . $request->user()->image;
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $file = $request->file('image');
            $filename = Str::random(12) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assets/profile'), $filename);
            $request->user()->image = $filename;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('success', 'Profile updated.');
    }
}
