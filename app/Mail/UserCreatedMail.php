<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $resetUrl;
    public $bannerUrl;
    public $profilePictureUrl;

    public function __construct(User $user, string $resetUrl, ?string $bannerUrl = null, ?string $profilePictureUrl = null)
    {
        $this->user = $user;
        $this->resetUrl = $resetUrl;
        $this->bannerUrl = $bannerUrl;
        $this->profilePictureUrl = $profilePictureUrl;
    }

    public function build()
    {
        return $this->subject('You were added to ' . config('app.name'))
                    ->view('emails.user.created')
                    ->with([
                        'user' => $this->user,
                        'resetUrl' => $this->resetUrl,
                        'bannerUrl' => $this->bannerUrl,
                        'profilePictureUrl' => $this->profilePictureUrl,
                    ]);
    }
}
