<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class InspectionScheduleNotification extends Notification
{
    use Queueable;

    protected $inspection;

    /**
     * Create a new notification instance.
     */
    public function __construct($inspection)
    {
        $this->inspection = $inspection;
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
    // public function toMail(object $notifiable): MailMessage
    // {
        // 
    // }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // 画像パスの設定
        $defaultDisk = Storage::disk();
    
        $imagePath = $defaultDisk->url('items/' . $this->inspection->item->image1);
        if (!$this->inspection->item->image1 || !$defaultDisk->exists('items/' . $this->inspection->item->image1)) {
            $imagePath = $defaultDisk->url('items/No_Image.jpg');
        }

        // ここで表示するのに必要な情報を詰め込む
        return [
            'id' => $this->inspection->item->id,
            'management_id' => $this->inspection->item->management_id,
            'image_path1' => $imagePath, //絶対URL
            'item_name' => $this->inspection->item->name,
            'scheduled_date' => $this->inspection ? $this->inspection->inspection_scheduled_date : null,
            'message' => '点検予定日が近づいています'
        ];
    }
}
