<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class LowStockNotification extends Notification
{
    use Queueable;

    public $item;

    /**
     * Create a new notification instance.
     */
    public function __construct($item)
    {
        $this->item = $item;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // 
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // 画像パスの設定
        $imagePath = 'storage/items/' . $this->item->image1;
        if (!$this->item->image1 || !Storage::disk('public')->exists('items/' . $this->item->image1)) {
            $imagePath = 'storage/items/No_Image.jpg';
        }

        // ここで表示するのに必要な情報を詰め込む
        return [
            'id' => $this->item->id,
            'management_id' => $this->item->management_id,
            'image_path1' => asset($imagePath),
            'item_name' => $this->item->name,
            'quantity' => $this->item->stock,
            'minimum_stock' => $this->item->minimum_stock,
            'message' => '在庫数が通知在庫数以下になっています'
        ];
    }
}
