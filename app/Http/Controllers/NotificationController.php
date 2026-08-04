<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = auth()->user()->notifications();

        if ($request->filter === 'unread') {
            $query = auth()->user()->unreadNotifications();
        }

        $notifications = $query->paginate(10);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return back()->with('success', 'Notifikasi ditandai telah dibaca.');
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi berhasil ditandai telah dibaca.');
    }

    public function destroy($id)
    {
        auth()->user()->notifications()->findOrFail($id)->delete();

        return back()->with('success', 'Notifikasi berhasil dihapus.');
    }
}