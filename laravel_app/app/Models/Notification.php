<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification;

/**
 * Peta ke tabel `notifications` (channel database).
 * Relasi `notifiable()` diwariskan: morph ke User (atau model lain).
 */
class Notification extends DatabaseNotification
{
}
