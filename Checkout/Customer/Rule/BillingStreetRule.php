<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Customer\Rule;

use Shopware\Core\Checkout\CheckoutRuleScope;
use Shopware\Core\Framework\Rule\Exception\UnsupportedValueException;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Rule\RuleComparison;
use Shopware\Core\Framework\Rule\RuleConfig;
use Shopware\Core\Framework\Rule\RuleConstraints;
use Shopware\Core\Framework\Rule\RuleScope;

class BillingStreetRule extends Rule
{
    /**
     * @var string|null
     */
    protected $streetName;

    /**
     * @var string
     */
    protected $operator;

    /**
     * @internal
     */
    public function __construct(string $operator = self::OPERATOR_EQ, ?string $streetName = null)
    {
        parent::__construct();
        $this->operator = $operator;
        $this->streetName = $streetName;
    }

    public function match(RuleScope $scope): bool
    {
        if (!$scope instanceof CheckoutRuleScope) {
            return false;
        }

        if (!$customer = $scope->getSalesChannelContext()->getCustomer()) {
            return false;
        }

        if (!$address = $customer->getActiveBillingAddress()) {
            return false;
        }

        if (!\is_string($this->streetName) && $this->operator !== self::OPERATOR_EMPTY) {
            throw new UnsupportedValueException(\gettype($this->streetName), self::class);
        }

        return RuleComparison::string($address->getStreet(), $this->streetName ?? '', $this->operator);
    }

    public function getConstraints(): array
    {
        $constraints = [
            'operator' => RuleConstraints::stringOperators(),
        ];

        if ($this->operator === self::OPERATOR_EMPTY) {
            return $constraints;
        }

        $constraints['streetName'] = RuleConstraints::string();

        return $constraints;
    }

    public function getName(): string
    {
        return 'customerBillingStreet';
    }

    public function getConfig(): RuleConfig
    {
        return (new RuleConfig())
            ->operatorSet(RuleConfig::OPERATOR_SET_STRING, true)
            ->stringField('streetName');
    }
}
