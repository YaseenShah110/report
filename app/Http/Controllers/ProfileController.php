<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Profile Controller
 * 
 * Handles user profile management:
 * - Display profile form
 * - Update profile information (name, email, password)
 * - Delete user account
 * 
 * Uses Laravel Breeze/Inertia conventions.
 */
class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     * Shows current name, email, and optional password change fields.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status'          => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     * If email is changed, marks email as unverified.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Fill user with validated data
        $request->user()->fill($request->validated());

        // If email changed, reset verification
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        // If password was provided, update it separately
        if ($request->filled('password')) {
            $request->user()->update([
                'password' => bcrypt($request->password)
            ]);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     * Requires current password confirmation.
     * Logs out the user after deletion.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Validate current password
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Logout the user
        Auth::logout();

        // Soft delete the user
        $user->delete();

        // Invalidate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}