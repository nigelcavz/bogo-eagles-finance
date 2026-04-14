<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canManageAnnouncements();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'create_event' => ['nullable', 'boolean'],
            'event_id' => ['nullable', 'integer', 'exists:events,id', Rule::prohibitedIf(fn () => $this->boolean('create_event'))],
            'event_title' => ['nullable', 'required_if:create_event,1', 'string', 'max:255'],
            'event_description' => ['nullable', 'string'],
            'event_date' => ['nullable', 'required_if:create_event,1', 'date'],
            'event_start_time' => ['nullable', 'date_format:H:i'],
            'event_end_time' => ['nullable', 'date_format:H:i', 'after:event_start_time'],
            'event_location' => ['nullable', 'string', 'max:255'],
            'visibility' => ['required', Rule::in(['all'])],
            'is_published' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'create_event' => $this->boolean('create_event'),
            'is_published' => $this->boolean('is_published'),
            'visibility' => $this->input('visibility', 'all'),
        ]);
    }
}
