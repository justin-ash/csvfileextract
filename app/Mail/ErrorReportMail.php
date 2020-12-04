<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ErrorReportMail extends Mailable {
	use Queueable, SerializesModels;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct() {
		//
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build() {
		return $this->from('leafcodesteam@gmail.com', 'Mailtrap')
			->subject('Test Queued Email')
			->view('mails.error');
	}
}
