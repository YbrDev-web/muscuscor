<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class DefiTermineNotification extends Notification
{
    public $defi;

    public function __construct($defi)
    {
        $this->defi = $defi;
    }

    public function via($notifiable)
    {
        return ['database']; // Utilisation du canal base de données
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => "Félicitations, tu as terminé le défi: " . $this->defi->nom,
            'defi_id' => $this->defi->id,
        ];
    }
}
