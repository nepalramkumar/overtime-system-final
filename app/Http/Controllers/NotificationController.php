<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // एउटा notification click गर्दा read mark गरेर सम्बन्धित page मा पठाउने
    public function markRead($id)
    {
        $notification = auth()->user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
            $url = $notification->data['url'] ?? route('dashboard');
            return redirect($url);
        }

        return redirect()->route('dashboard');
    }

    // सबै notification एकैचोटि read mark गर्ने
    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return redirect()->back();
    }
}
