<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendFileNotification extends Notification
{
    use Queueable;
    public $url;
    public $subject;
    public $message;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($url , $subject , $message)
    {
        $this->url = $url;
        $this->subject = $subject == null ? "DROP | Someone send you a File" : $subject;
        $this->message = $message == null ? "Please click on the link below to download the file" : $message;
    }
    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject($this->subject)
                    ->line('Someone send you a file')
                    ->line($this->message)
                    ->action('Download File', $this->url)
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
