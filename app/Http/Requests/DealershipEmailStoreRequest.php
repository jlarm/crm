<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Actions\Emails\SanitizeEmailHtml;
use App\Models\Dealership;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class DealershipEmailStoreRequest extends FormRequest
{
    public const int MAX_ATTACHMENTS_KILOBYTES = 10 * 1024;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $dealership = $this->route('dealership');
        $dealershipId = $dealership instanceof Dealership ? $dealership->id : 0;

        return [
            'contact_id' => [
                'required',
                'integer',
                Rule::exists('contacts', 'id')->where('dealership_id', $dealershipId)->whereNotNull('email')->where('email', '!=', ''),
            ],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:100000'],
            'parent_id' => ['nullable', 'integer', Rule::exists('dealership_emails', 'id')->where('dealership_id', $dealershipId)],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'mimes:pdf,png,jpg,jpeg,gif,doc,docx,xls,xlsx,csv,txt', 'max:'.self::MAX_ATTACHMENTS_KILOBYTES],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'contact_id.exists' => 'Pick a contact at this dealership who has an email address.',
            'attachments.*.mimes' => 'Attachments must be PDF, image, Word, Excel, CSV or text files.',
            'attachments.*.max' => 'Each attachment must be 10 MB or smaller.',
        ];
    }

    /**
     * The message as safe HTML, limited to the editor's formatting.
     */
    public function sanitizedBody(): string
    {
        return (new SanitizeEmailHtml)($this->string('body')->value());
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->user()?->email === null || $this->user()->email === '') {
                    $validator->errors()->add('contact_id', 'Add an email address to your profile before sending email.');
                }

                if (is_string($this->input('body')) && $this->sanitizedBody() === '') {
                    $validator->errors()->add('body', 'Write a message.');
                }

                $totalBytes = collect($this->file('attachments', []))
                    ->filter(fn (mixed $file): bool => $file instanceof UploadedFile)
                    ->sum(fn (UploadedFile $file): int => (int) $file->getSize());

                if ($totalBytes > self::MAX_ATTACHMENTS_KILOBYTES * 1024) {
                    $validator->errors()->add('attachments', 'Attachments must add up to 10 MB or less.');
                }
            },
        ];
    }
}
