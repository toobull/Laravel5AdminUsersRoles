<?php namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Auth;

class UserSearchRequest extends Request {

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return Auth::user()->is_admin;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
            'id' => '',
			'name' => '',
            'email' => '',
            'is_admin' =>'',
            'roles' => '',
		];
	}

}
