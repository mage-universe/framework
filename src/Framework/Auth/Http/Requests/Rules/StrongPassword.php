<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Http\Requests\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** @infection-ignore-all */
readonly class StrongPassword implements ValidationRule
{
    public function __construct(private string $prefix = 'validation') {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = (string) $value;
        if (strlen($value) < 12) {
            $fail($this->prefix . '.min.string')->translate(['attribute' => $attribute, 'min' => 12]);
        }
        if (!preg_match('/[A-Za-z]/', $value) || !preg_match('/[0-9]/', $value)) {
            $fail($this->prefix . '.letters_and_numbers')->translate(['attribute' => $attribute]);
        }
        if (!preg_match('/[a-z]/', $value) || !preg_match('/[A-Z]/', $value)) {
            $fail($this->prefix . '.uppercase_and_lowercase')->translate(['attribute' => $attribute]);
        }
        if (!preg_match('/[^\w\s]/', $value)) {
            $fail($this->prefix . '.special_characters')->translate(['attribute' => $attribute]);
        }
        if (str_contains($value, ' ')) {
            $fail($this->prefix . '.no_spaces')->translate(['attribute' => $attribute]);
        }
    }
}
