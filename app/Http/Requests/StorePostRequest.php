<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        // route is behind auth middleware; still guard here
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'track' => ['required', 'array'],

            'track.id'          => ['nullable', 'string', 'max:100'],
            'track.title'       => ['required', 'string', 'max:255'],
            'track.artist'      => ['required', 'string', 'max:255'],

            'track.coverUrl'    => ['required', 'url', 'max:2048'],
            'track.previewUrl'  => ['nullable', 'url', 'max:2048'],
            'track.externalUrl' => ['nullable', 'url', 'max:2048'],

            'track.durationMs'  => ['nullable', 'integer', 'min:0'],

            // Optional caption stored inside the JSON "track"
            'track.caption'     => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'track.title.required'   => 'Please provide the track title.',
            'track.artist.required'  => 'Please provide the artist name.',
            'track.coverUrl.required'=> 'A cover image URL is required.',
        ];
    }
}