<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGuideRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Guide::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'          => ['required', 'string', 'max:255'],
            'slug'           => ['nullable', 'string', 'max:255', 'unique:guides,slug'],
            'content'        => ['required', 'string'],
            'excerpt'        => ['nullable', 'string', 'max:500'],
            'category_id'    => ['nullable', 'integer', 'exists:guide_categories,id'],
            'role_target'    => ['nullable'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status'         => ['required', Rule::in(['draft', 'published', 'archived'])],
            'order'          => ['nullable', 'integer', 'min:0'],
            'is_featured'    => ['boolean'],
            'metadata'       => ['nullable', 'json'],
            'published_at'   => ['nullable', 'date'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required'       => 'Judul panduan wajib diisi.',
            'title.max'            => 'Judul panduan maksimal 255 karakter.',
            'content.required'     => 'Konten panduan wajib diisi.',
            'slug.unique'          => 'Slug sudah digunakan, silakan gunakan slug lain.',
            'category_id.exists'   => 'Kategori yang dipilih tidak valid.',
            'featured_image.image' => 'File harus berupa gambar (jpg, jpeg, png, webp).',
            'featured_image.max'   => 'Ukuran gambar maksimal 2MB.',
            'status.required'      => 'Status panduan wajib dipilih.',
            'status.in'            => 'Status harus salah satu: draft, published, atau archived.',
        ];
    }
}
