<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProjectAutoPausedNotifier extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    /**
     * @var string
     */
    public $projectCode;

    /**
     * @var string
     */
    public $link;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $projectCode) {
        $this->projectCode = $projectCode;
        $this->link = route('admin.projects.target_track.index', ['projectCode' => $projectCode]);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): ProjectAutoPausedNotifier {
        return $this->subject($this->projectCode . ' is auto-paused - limit reached')
            ->markdown('emails.admin.project_auto_paused_notifier');
    }
}
