<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ContactSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ContactController
{
    public function show(): View
    {
        return view('pages.contact');
    }

    public function submit(Request $request): RedirectResponse
    {
        // Honeypot check: if the hidden field is filled, it's a bot
        if ($request->filled('website_url')) {
            // Silently redirect to avoid revealing the trap
            return redirect()->route('contact')->with('success', 'Uw bericht is verzonden. Wij nemen zo snel mogelijk contact met u op.');
        }

        // Timestamp check: form must take at least 3 seconds to fill
        $formLoadedAt = (int) $request->input('_form_token', 0);
        if ($formLoadedAt > 0 && (time() - $formLoadedAt) < 3) {
            return redirect()->route('contact')->with('success', 'Uw bericht is verzonden. Wij nemen zo snel mogelijk contact met u op.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'min:2'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ], [
            'name.required' => 'Vul uw naam in.',
            'name.min' => 'Uw naam moet minimaal 2 tekens bevatten.',
            'email.required' => 'Vul uw e-mailadres in.',
            'email.email' => 'Vul een geldig e-mailadres in.',
            'message.required' => 'Vul uw bericht in.',
            'message.min' => 'Uw bericht moet minimaal 10 tekens bevatten.',
        ]);

        // Simple spam detection: check for excessive links
        $linkCount = preg_match_all('/https?:\/\//i', $validated['message']);
        $isSpam = $linkCount > 3;

        ContactSubmission::create([
            ...$validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'is_spam' => $isSpam,
        ]);

        return redirect()->route('contact')->with('success', 'Bedankt voor uw bericht! Wij nemen zo snel mogelijk contact met u op.');
    }
}