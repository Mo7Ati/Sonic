<?php

namespace Modules\Cashier\Http\Requests\Branch;

use App\Enums\BranchStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBranchStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(array_column(BranchStatusEnum::cases(), 'value'))],
        ];
    }
}
