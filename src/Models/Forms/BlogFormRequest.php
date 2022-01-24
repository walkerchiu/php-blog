<?php

namespace WalkerChiu\Blog\Models\Forms;

use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use WalkerChiu\Core\Models\Forms\FormRequest;

class BlogFormRequest extends FormRequest
{
    /**
     * @Override Illuminate\Foundation\Http\FormRequest::getValidatorInstance
     */
    protected function getValidatorInstance()
    {
        $request = Request::instance();
        $data = $this->all();
        if (
            $request->isMethod('put')
            && empty($data['id'])
            && isset($request->id)
        ) {
            $data['id'] = (string) $request->id;
            $this->getInputSource()->replace($data);
        }

        return parent::getValidatorInstance();
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return Array
     */
    public function attributes()
    {
        return [
            'user_id'        => trans('php-blog::blog.user_id'),
            'identifier'     => trans('php-blog::blog.identifier'),
            'language'       => trans('php-blog::blog.language'),
            'script_head'    => trans('php-blog::blog.script_head'),
            'script_footer'  => trans('php-blog::blog.script_footer'),
            'options'        => trans('php-blog::blog.options'),
            'is_highlighted' => trans('php-blog::blog.is_highlighted'),
            'is_enabled'     => trans('php-blog::blog.is_enabled'),

            'name'           => trans('php-blog::blog.name'),
            'description'    => trans('php-blog::blog.description'),
            'keywords'       => trans('php-blog::blog.keywords')
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return Array
     */
    public function rules()
    {
        $rules = [
            'user_id'        => ['nullable','string','exists:'.config('wk-core.table.user').',id'],
            'identifier'     => 'required|string|max:255',
            'language'       => ['nullable', Rule::in(config('wk-core.class.core.language')::getCodes())],
            'script_head'    => '',
            'script_footer'  => '',
            'options'        => 'nullable|json',
            'is_highlighted' => 'required|boolean',
            'is_enabled'     => 'required|boolean',

            'name'           => 'required|string|max:255',
            'description'    => '',
            'keywords'       => ''
        ];

        $request = Request::instance();
        if (
            $request->isMethod('put')
            && isset($request->id)
        ) {
            $rules = array_merge($rules, ['id' => ['required','string','exists:'.config('wk-core.table.blog.blogs').',id']]);
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return Array
     */
    public function messages()
    {
        return [
            'id.required'             => trans('php-core::validation.required'),
            'id.string'               => trans('php-core::validation.string'),
            'id.exists'               => trans('php-core::validation.exists'),
            'user_id.string'          => trans('php-core::validation.string'),
            'user_id.exists'          => trans('php-core::validation.exists'),
            'identifier.required'     => trans('php-core::validation.required'),
            'identifier.max'          => trans('php-core::validation.max'),
            'language.in'             => trans('php-core::validation.in'),
            'options.json'            => trans('php-core::validation.json'),
            'is_highlighted.required' => trans('php-core::validation.required'),
            'is_highlighted.boolean'  => trans('php-core::validation.boolean'),
            'is_enabled.required'     => trans('php-core::validation.required'),
            'is_enabled.boolean'      => trans('php-core::validation.boolean'),

            'name.required'           => trans('php-core::validation.required'),
            'name.string'             => trans('php-core::validation.string'),
            'name.max'                => trans('php-core::validation.max')
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after( function ($validator) {
            $data = $validator->getData();
            if (isset($data['identifier'])) {
                $result = config('wk-core.class.blog.blog')::where('identifier', $data['identifier'])
                                ->when(isset($data['id']), function ($query) use ($data) {
                                    return $query->where('id', '<>', $data['id']);
                                  })
                                ->exists();
                if ($result)
                    $validator->errors()->add('identifier', trans('php-core::validation.unique', ['attribute' => trans('php-blog::blog.identifier')]));
            }
        });
    }
}
