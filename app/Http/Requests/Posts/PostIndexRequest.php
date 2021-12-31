<?php

namespace App\Http\Requests\Posts;

use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostIndexRequest extends FormRequest
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
            'status'    => ['nullable', Rule::in([Post::STATUS_ACTIVE, Post::STATUS_INACTIVE])],
            'from_time' => 'nullable|integer',
            'to_time'   => 'nullable|integer',
            'page'      => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'status.in'    => 'Status is invalid',
            'page.integer' => 'Page must be a number',
            'page.min'     => 'Page min is 1',
            'from_time.integer' => 'From time must be a number',
            'to_time.integer'   => 'To time must be a number',
            'to_time.gt'        => 'To time must be greater than from time'
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('from_time') && '' != $this->input('from_time')) {
            $this->merge(['from_time' => Carbon::createFromDate($this->input('from_time'))->timestamp]);
        }

        if ($this->has('to_time') && '' != $this->input('to_time')) {
            $this->merge(['to_time' => Carbon::createFromDate($this->input('to_time'))->timestamp]);
        }
    }
}
