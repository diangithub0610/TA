<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Pelanggan;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $Pelanggan;
    public $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Pelanggan $Pelanggan, $token)
    {
        $this->Pelanggan = $Pelanggan;
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Reset Password')
                    ->view('emails.reset-password');
    }
}