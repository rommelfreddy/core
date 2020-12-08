<?php declare(strict_types=1);

namespace Shopware\Core\Framework\DataAbstractionLayer\FieldSerializer;

use Shopware\Core\Checkout\Cart\Price\Struct\AbsolutePriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\PercentagePriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRule;
use Shopware\Core\Content\Rule\DataAbstractionLayer\Indexing\ConditionTypeNotFound;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InvalidPriceFieldTypeException;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopware\Core\Framework\DataAbstractionLayer\Write\DataStack\KeyValuePair;
use Shopware\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteParameterBag;
use Shopware\Core\Framework\Rule\Collector\RuleConditionRegistry;
use Shopware\Core\Framework\Rule\Container\Container;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Validation\WriteConstraintViolationException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PriceDefinitionFieldSerializer extends JsonFieldSerializer
{
    /**
     * @var RuleConditionRegistry
     */
    private $ruleConditionRegistry;

    public function __construct(
        DefinitionInstanceRegistry $compositeHandler,
        ValidatorInterface $validator,
        RuleConditionRegistry $ruleConditionRegistry
    ) {
        parent::__construct($compositeHandler, $validator);

        $this->ruleConditionRegistry = $ruleConditionRegistry;
    }

    public function encode(
        Field $field,
        EntityExistence $existence,
        KeyValuePair $data,
        WriteParameterBag $parameters
    ): \Generator {
        $value = json_decode(json_encode($data->getValue(), \JSON_PRESERVE_ZERO_FRACTION), true);

        if ($value !== null) {
            if (!\array_key_exists('type', $value)) {
                throw new InvalidPriceFieldTypeException('none');
            }

            switch ($value['type']) {
                case QuantityPriceDefinition::TYPE:
                    $this->validateProperties(
                        $value,
                        QuantityPriceDefinition::getConstraints(),
                        $parameters->getPath()
                    );
                    if (!\array_key_exists('taxRules', $value)) {
                        break;
                    }

                    foreach ($value['taxRules'] as $key => $taxRule) {
                        $this->validateProperties($taxRule, TaxRule::getConstraints(), $parameters->getPath() . '/taxRules/' . $key);
                    }

                    break;
                case AbsolutePriceDefinition::TYPE:
                    $this->validateProperties(
                        $value,
                        AbsolutePriceDefinition::getConstraints(),
                        $parameters->getPath()
                    );

                    if (!\array_key_exists('filter', $value) || $value['filter'] === null) {
                        break;
                    }
                    $violations = $this->validateRules($value['filter'], $parameters->getPath() . '/filter');
                    if ($violations->count() > 0) {
                        throw new WriteConstraintViolationException($violations, $parameters->getPath());
                    }

                    break;
                case PercentagePriceDefinition::TYPE:
                    $this->validateProperties(
                        $value,
                        PercentagePriceDefinition::getConstraints(),
                        $parameters->getPath()
                    );

                    if (!\array_key_exists('filter', $value) || $value['filter'] === null) {
                        break;
                    }
                    $violations = $this->validateRules($value['filter'], $parameters->getPath() . '/filter');
                    if ($violations->count() > 0) {
                        throw new WriteConstraintViolationException($violations, $parameters->getPath());
                    }

                    break;
                default:
                    throw new InvalidPriceFieldTypeException($value['type']);
            }

            unset($value['extensions']);
        }

        $data->setValue($value);

        yield from parent::encode($field, $existence, $data, $parameters);
    }

    public function decode(Field $field, $value)
    {
        if ($value === null) {
            return null;
        }

        $value = parent::decode($field, $value);

        if (!\array_key_exists('type', $value)) {
            throw new InvalidPriceFieldTypeException('none');
        }

        switch ($value['type']) {
            case QuantityPriceDefinition::TYPE:
                return QuantityPriceDefinition::fromArray($value);
            case AbsolutePriceDefinition::TYPE:
                $rules = (\array_key_exists('filter', $value) && $value['filter'] !== null) ? $this->decodeRule($value['filter']) : null;

                return new AbsolutePriceDefinition($value['price'], $value['precision'], $rules);
            case PercentagePriceDefinition::TYPE:
                $rules = \array_key_exists('filter', $value) && $value['filter'] !== null ? $this->decodeRule($value['filter']) : null;

                return new PercentagePriceDefinition($value['percentage'], $value['precision'], $rules);
        }

        throw new InvalidPriceFieldTypeException($value['type']);
    }

    private function validateRules(array $data, string $basePath): ConstraintViolationList
    {
        $violationList = new ConstraintViolationList();
        /** @var string|null $type */
        $type = null;
        if (\array_key_exists('_name', $data)) {
            $type = $data['_name'];
            unset($data['_name']);
        }

        if ($type === null) {
            $violationList->add(
                $this->buildViolation(
                    'This "_name" value (%value%) is invalid.',
                    ['%value%' => $type ?? 'NULL'],
                    null,
                    $basePath . '/_name'
                )
            );
        } else {
            $rule = $this->ruleConditionRegistry->getRuleInstance($type);
            // do not validate container
            if (!$rule instanceof Container) {
                $validations = $rule->getConstraints();
                $violationList->addAll($this->validateConsistence($basePath, $validations, $data));
            }
        }

        if (\array_key_exists('rules', $data)) {
            foreach ($data['rules'] as $rule) {
                $violationList->addAll($this->validateRules($rule, $basePath . '/' . $type));
            }
        }

        return $violationList;
    }

    private function decodeRule(array $rule): Rule
    {
        if (!$this->ruleConditionRegistry->has($rule['_name'])) {
            throw new ConditionTypeNotFound($rule['_name']);
        }

        $ruleClass = $this->ruleConditionRegistry->getRuleClass($rule['_name']);
        $object = new $ruleClass();

        if (\array_key_exists('rules', $rule) && $object instanceof Container) {
            foreach ($rule['rules'] as $item) {
                $object->addRule($this->decodeRule($item));
            }
        } else {
            /* @var Rule $object */
            $object->assign($rule);
        }

        return $object;
    }

    private function validateProperties(array $data, array $constraints, string $path): void
    {
        foreach ($constraints as $key => $constraint) {
            $value = $data[$key] ?? null;

            $this->validate(
                $constraint,
                new KeyValuePair($key, $value, true),
                $path
            );
        }
    }

    private function buildViolation(
        string $messageTemplate,
        array $parameters,
        $root = null,
        ?string $propertyPath = null,
        $invalidValue = null,
        $code = null
    ): ConstraintViolation {
        return new ConstraintViolation(
            str_replace(array_keys($parameters), array_values($parameters), $messageTemplate),
            $messageTemplate,
            $parameters,
            $root,
            $propertyPath,
            $invalidValue
        );
    }

    private function validateConsistence(string $basePath, array $fieldValidations, array $payload): ConstraintViolationList
    {
        $list = new ConstraintViolationList();
        foreach ($fieldValidations as $fieldName => $validations) {
            $currentPath = sprintf('%s/%s', $basePath, $fieldName);
            $list->addAll(
                $this->validator->startContext()
                    ->atPath($currentPath)
                    ->validate($payload[$fieldName] ?? null, $validations)
                    ->getViolations()
            );
        }

        foreach ($payload as $fieldName => $_value) {
            $currentPath = sprintf('%s/%s', $basePath, $fieldName);

            if (!\array_key_exists($fieldName, $fieldValidations)) {
                $list->add(
                    $this->buildViolation(
                        'The property "{{ fieldName }}" is not allowed.',
                        ['{{ fieldName }}' => $fieldName],
                        null,
                        $currentPath
                    )
                );
            }
        }

        return $list;
    }
}
