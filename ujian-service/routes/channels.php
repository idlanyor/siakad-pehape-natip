<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\SesiUjian;
use App\Models\Ujian;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/*
|--------------------------------------------------------------------------
| Proktor Channels
|--------------------------------------------------------------------------
|
| Channel authorization untuk realtime proktor monitoring.
| Hanya guru/admin yang mengajar ujian tersebut yang bisa subscribe.
|
*/

// Channel untuk monitoring sesi ujian tertentu
Broadcast::channel('proktor.sesi.{sesiUjianId}', function ($user, $sesiUjianId) {
    $sesi = SesiUjian::find($sesiUjianId);
    
    if (!$sesi) {
        return false;
    }
    
    // Allow if user is guru of the ujian or admin
    return in_array($user['role'], ['guru', 'admin', 'superadmin']) && 
           ($user['user_id'] == $sesi->ujian->guru_id || in_array($user['role'], ['admin', 'superadmin']));
});

// Channel untuk monitoring semua sesi dalam ujian tertentu
Broadcast::channel('proktor.ujian.{ujianId}', function ($user, $ujianId) {
    $ujian = Ujian::find($ujianId);
    
    if (!$ujian) {
        return false;
    }
    
    // Allow if user is guru of the ujian or admin
    return in_array($user['role'], ['guru', 'admin', 'superadmin']) && 
           ($user['user_id'] == $ujian->guru_id || in_array($user['role'], ['admin', 'superadmin']));
});
