<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\URL;
use RuntimeException;

class ChangeEmail extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    /**
     * @var string
     */
    public $temporarySignedRoute;

    /**
     * @var string
     */
    public $newEmail;

    /**
     * @var string
     */
    public $currentEmail;

    /**
     * Create a new message instance.
     *
     * @param  int|string  $userId
     * @param  string  $newEmail
     */
    public function __construct($userId, string $newEmail) {
        if ( ! $user = User::find($userId)) {
            throw new RuntimeException('Could not find the user by ID to change the email address.');
        }

        $this->currentEmail = $user->email;
        $this->temporarySignedRoute = url()->temporarySignedRoute('profile.change-email.verify', 60 * 60, [
            'new_email' => $newEmail,
        ]);
        $this->newEmail = $newEmail;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): ChangeEmail {
        return $this->subject(__('email/change_email.subject'))
            ->markdown('emails.change-email');
    }
}
