<?php

namespace App\Jobs;

use App\Imports\ValidateFile;
use App\Mail\ErrorReportMail;
use App\Model\ModuleModel as Module;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;

class FileExtract implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected $fileName;
	protected $config;
	protected $details;

	public $errors = [];

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($fileName, $config, $details) {
		$this->fileName = $fileName;
		$this->config = $config;
		$this->details = $details;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle() {
		$csvFilelData = $this->readFile($this->fileName);
		$formattedMessages = [];
		foreach ($this->errors as $key => $errors) {
			foreach ($errors as $key1 => $error) {
				if (!empty($formattedMessages[$error])) {
					$formattedMessages[$error] = $formattedMessages[$error] . ', ' . $key;
				} else {
					$formattedMessages[$error] = $error . ' at row ' . $key;
				}
			}
		}
		$emailMessage = json_encode(array_values($formattedMessages), JSON_PRETTY_PRINT);
		if (empty(json_decode($emailMessage))) {
			$emailMessage = "Your file has been successfully imported to database";
		}

		$email = new ErrorReportMail();
		try {

			$mail = Mail::send('mails.error', array('body' => $emailMessage), function ($message) {
				$message->to('charush@accubits.com')->subject('CSV File Vlidation Report');

			});

		} catch (Exception $e) {
			die($e->getMessage());
		}
	}

	function readFile($filename) {

		if (!file_exists($filename) || !is_readable($filename)) {
			return false;
		}

		$validator = new ValidateFile();
		$header = null;
		$data = array();
		$numRow = 0;
		if (($handle = fopen($filename, 'r')) !== false) {
			while (($row = fgetcsv($handle, 1000)) !== false) {

				if (!$header) {
					$header = $row;
				} else {
					$this->validateData($row, $numRow);
					$data[] = array_combine($header, $row);
					$numRow++;
				}

			}
			fclose($handle);
		}
		return $data;

	}

	function validateData($row, $numRow) {
		$rowKeys = array_keys($this->config);
		$resData = array_combine($rowKeys, $row);
		$validationRules = array_combine($rowKeys, $this->rules());

		$validator = \Validator::make($resData, $validationRules, $this->validationMessage());
		if ($validator->fails()) {
			$this->errors[$numRow] = $validator->errors()->all();
		} else {

			$oModule = new Module();
			$oModule->mdule_code = $row[0];
			$oModule->mdule_name = $row[1];
			$oModule->mdule_term = $row[2];
			$oModule->created_at = date('Y-m-d H:i:s');
			$oModule->updated_at = date('Y-m-d H:i:s');
			$oModule->save();
		}

	}

	function rules() {
		return [
			'0' => [
				'required',
				'string',
				'regex:/^[^(|\\]~!%^&*=};:?><’)]*$/',
			],
			'1' => [
				'required',
				'string',
				'regex:/^[^(|\\]~!%^&*=};:?><’)]*$/',
				// 'unique:users',
			],
			'2' => [
				'required',
				'string',
				'regex:/^[^(|\\]~!%^&*=};:?><’)]*$/',
			],
		];
	}

	function validationMessage() {
		return [
			'required' => 'The :attribute field is missing.',
			'regex' => 'The :attribute contains symbols',
		];
	}
}
