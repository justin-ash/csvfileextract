<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ValidateFile implements ToCollection {

	/**
	 * @var errors
	 */
	public $errors = [];

	/**
	 * @var isValidFile
	 */
	public $isValidFile = false;

	/**
	 * ValidateCsvFile constructor.
	 * @param StoreEntity $store
	 */
	public function __construct() {
		//
	}

	/**
	 * @param Collection $collection
	 */

	public function collection(Collection $rows) {
		$errors = [];
		if (count($rows) > 1) {
			$rows = $rows->slice(1);
			foreach ($rows as $key => $row) {
				print_r($row);
				$validator = \Validator::make($row->toArray(), [
					'0' => [
						'required',
						'string',
						'max:255',
					],
					'1' => [
						'required',
						'string',
						'email',
						'max:255',
						// 'unique:users',
					],
					'2' => [
						'required',
						'string',
						'min:6',
					],
				]);

				if ($validator->fails()) {
					$errors[$key] = $validator->errors();
				}
			}

			$this->errors = $errors;
			$this->isValidFile = true;
		}
	}

	public function startRow(): int {
		return 1;
	}
}
