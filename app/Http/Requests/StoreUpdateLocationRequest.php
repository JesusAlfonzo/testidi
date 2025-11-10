<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateLocationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $locationId = $this->route('location') ? $this->route('location')->id : null;

        $uniqueName = ['required', 'string', 'max:100'];

        if ($locationId) {
            $uniqueName[] = 'unique:locations,name,' . $locationId;
        } else {
            $uniqueName[] = 'unique:locations,name';
        }

        return [
            'name' => $uniqueName,
            'details' => ['nullable', 'string', 'max:500'],
        ];
    }
}
