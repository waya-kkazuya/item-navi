<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RequestedItemNotification extends Notification
{
    use Queueable;

    protected $itemRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct($itemRequest)
    {
        $this->itemRequest = $itemRequest;
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
        return [
            'id'                     => $this->itemRequest->id,
            'item_name'              => $this->itemRequest->name,
            'requestor'              => $this->itemRequest->requestor,
            'remarks_from_requestor' => $this->itemRequest->remarks_from_requestor,
            'message'                => '備品のリクエストが追加されました',
        ];
    }
}
