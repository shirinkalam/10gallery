<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class SendOrderedImages extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(private array $images , private User $user)
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this
        ->view('mail.sendOrderedImages')
        ->with([
            'user' => $this->user,
        ]);

        foreach($this->images as $filePath){
            $email->attach(storage_path('app/local_storage/' . $filePath));
        }

        return $email;
    }
}
