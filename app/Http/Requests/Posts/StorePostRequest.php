<?php

namespace App\Http\Requests\Posts;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'title' => 'required',
            'content' => 'required',
            'publish_date' => 'integer|gt: ' . Carbon::now()->timestamp,
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'   => 'Title is required',
            'content.required' => 'Content is required',
            'publish_date.integer' => 'Thời gian publish phải là số',
            'publish_date.gt' => 'Thời gian publish phải lớn hơn thời gian hiện tại'
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('publish_date')) {
            $this->merge(['publish_date' => Carbon::createFromTimeString($this->input('publish_date'))->timestamp]);
        }
    }
}
