<?php

namespace WalkerChiu\Blog\Models\Forms;

use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use WalkerChiu\Core\Models\Forms\FormRequest;

class ArticleFormRequest extends FormRequest
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
            'blog_id'        => trans('php-blog::article.blog_id'),
            'newest_id'      => trans('php-blog::article.newest_id'),
            'identifier'     => trans('php-blog::article.identifier'),
            'can_comment'    => trans('php-blog::article.can_comment'),
            'can_search'     => trans('php-blog::article.can_search'),
            'is_highlighted' => trans('php-blog::article.is_highlighted'),
            'is_enabled'     => trans('php-blog::article.is_enabled'),
            'edit_at'        => trans('php-blog::article.edit_at'),

            'cover'          => trans('php-blog::article.cover'),
            'title'          => trans('php-blog::article.title'),
            'description'    => trans('php-blog::article.description'),
            'content'        => trans('php-blog::article.content'),
            'keywords'       => trans('php-blog::article.keywords')
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
            'blog_id'        => ['nullable','string','exists:'.config('wk-core.table.blog.blogs').',id'],
            'newest_id'      => ['nullable','string','exists:'.config('wk-core.table.blog.blogs_articles').',id'],
            'identifier'     => 'required|string|max:255',
            'can_comment'    => 'required|boolean',
            'can_search'     => 'required|boolean',
            'is_highlighted' => 'required|boolean',
            'is_enabled'     => 'required|boolean',
            'edit_at'        => 'required|date|date_format:Y-m-d H:i:s',

            'cover'          => 'url',
            'title'          => 'required|string|max:255',
            'description'    => 'required|string',
            'content'        => 'required|string',
            'keywords'       => ''
        ];

        $request = Request::instance();
        if (
            $request->isMethod('put')
            && isset($request->id)
        ) {
            $rules = array_merge($rules, ['id' => ['required','string','exists:'.config('wk-core.table.blog.blogs_articles').',id']]);
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
            'blog_id.string'          => trans('php-core::validation.string'),
            'blog_id.exists'          => trans('php-core::validation.exists'),
            'newest_id.string'        => trans('php-core::validation.string'),
            'newest_id.exists'        => trans('php-core::validation.exists'),
            'identifier.required'     => trans('php-core::validation.required'),
            'identifier.max'          => trans('php-core::validation.max'),
            'can_comment.required'    => trans('php-core::validation.required'),
            'can_comment.boolean'     => trans('php-core::validation.boolean'),
            'can_search.required'     => trans('php-core::validation.required'),
            'can_search.boolean'      => trans('php-core::validation.boolean'),
            'is_highlighted.required' => trans('php-core::validation.required'),
            'is_highlighted.boolean'  => trans('php-core::validation.boolean'),
            'is_enabled.required'     => trans('php-core::validation.required'),
            'is_enabled.boolean'      => trans('php-core::validation.boolean'),
            'edit_at.required'        => trans('php-core::validation.required'),
            'edit_at.date'            => trans('php-core::validation.date'),
            'edit_at.date_format'     => trans('php-core::validation.date_format'),

            'cover.url'               => trans('php-core::validation.url'),
            'title.required'          => trans('php-core::validation.required'),
            'title.string'            => trans('php-core::validation.string'),
            'title.max'               => trans('php-core::validation.max'),
            'description.required'    => trans('php-core::validation.required'),
            'description.string'      => trans('php-core::validation.string'),
            'content.required'        => trans('php-core::validation.required'),
            'content.string'          => trans('php-core::validation.string')
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
                $result = config('wk-core.class.blog.article')::where('identifier', $data['identifier'])
                                ->when(isset($data['blog_id']), function ($query) use ($data) {
                                    return $query->where('blog_id', $data['blog_id']);
                                  })
                                ->when(isset($data['id']), function ($query) use ($data) {
                                    return $query->where('id', '<>', $data['id']);
                                  })
                                ->exists();
                if ($result)
                    $validator->errors()->add('identifier', trans('php-core::validation.unique', ['attribute' => trans('php-blog::article.identifier')]));
            }
        });
    }
}
