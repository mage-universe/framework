<?php declare(strict_types=1);

namespace Mage\Framework\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationRuleParser;
use function Lambdish\Phunctional\first;
use function Lambdish\Phunctional\reduce;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 * @psalm-method array<string,string> input(string|null $key = null, mixed $default = null)
 * @psalm-method array<string,mixed> validated($key = null, $default = null)
 */
abstract class BaseRequest extends FormRequest
{
    abstract public function rules(): array;

    public function messages(): array
    {
        /** @psalm-var object{rules:array} $input */
        $input = (new ValidationRuleParser($this->input()))
            ->explode($this->rules());

        /** @psalm-var array */
        return reduce(function (array $acc, array $rules, string $field): array {
            /** @psalm-var array $rules */
            $rules = reduce(function (array $acc, mixed $rule) use ($field): array {
                if (!is_string($rule)) {
                    return $acc;
                }
                /** @psalm-var string $rule */
                $rule = first(explode(':', $rule));
                $field = implode('.', [$field, $rule]);
                $acc[$field] = trans(implode('.', [$this->prefix(), $rule]));
                return $acc;
            }, $rules, []);
            return [...$acc, ...$rules];
        }, $input->rules, []);
    }

    protected function prefix(): string
    {
        return 'validation';
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        throw (new ValidationException($validator))
            ->errorBag($this->errorBag)
            ->redirectTo($this->getRedirectUrl());
    }
}
