<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateBrandRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $brandId = $this->route('brand') ? $this->route('brand')->id : null;

        $uniqueName = ['required', 'string', 'max:100'];

        if ($brandId) {
            $uniqueName[] = 'unique:brands,name,' . $brandId;
        } else {
            $uniqueName[] = 'unique:brands,name';
        }

        return [
            'name' => $uniqueName,
            'website' => ['nullable', 'url', 'max:255'],
        ];
    }
}
