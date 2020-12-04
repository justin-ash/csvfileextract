<?php

namespace App\Http\Controllers;
use App\Jobs\FileExtract;
use Illuminate\Http\Request;

class FileExtractController extends Controller {

	/**
	 * Recive the post data file
	 * give to the job to validate and save to database
	 *
	 * @param  Null
	 * @return Response
	 */

	public function index(Request $request) {

		$file = $request->file('file');
		$validator = \Validator::make([
			'file' => $file,
			'extension' => strtolower($file->getClientOriginalExtension()),
		], $this->rules());

		if ($validator->fails()) {
			return response()->json($validator->errors(), 422);
		}

		$destinationPath = 'uploads/';
		$file->move($destinationPath, $file->getClientOriginalName());
		$fileName = $destinationPath . $file->getClientOriginalName();
		FileExtract::dispatch($fileName, $this->fileConfig(), $this->mailConfig());

		return \Response::json([
			'status' => 'success',
			'message' => 'File processing, report will send you soon!',
		]);
	}

	public function rules() {
		return [
			'file' => 'required',
			'extension' => 'required|in:csv',
		];
	}

	function fileConfig() {
		return [
			'Module_Code' => 'Module Code',
			'Module_Name' => 'Module Name',
			'Term_Name' => 'Term name',
		];
	}

	function mailConfig() {
		return [
			'email' => 'justinjoseph287@gmail.com',
		];
	}
}
