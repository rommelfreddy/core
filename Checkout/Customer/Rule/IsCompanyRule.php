<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Customer\Rule;

use Shopware\Core\Checkout\CheckoutRuleScope;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Rule\RuleConstraints;
use Shopware\Core\Framework\Rule\RuleScope;

class IsCompanyRule extends Rule
{
    /**
     * @var bool
     */
    protected $isCompany;

    /**
     * @internal
     */
    public function __construct(bool $isCompany = true)
    {
        parent::__construct();
        $this->isCompany = $isCompany;
    }

    public function match(RuleScope $scope): bool
    {
        if (!$scope instanceof CheckoutRuleScope) {
            return false;
        }

        if (!$customer = $scope->getSalesChannelContext()->getCustomer()) {
            return false;
        }

        if ($this->isCompany) {
            return (bool) $customer->getCompany();
        }

        return !$customer->getCompany();
    }

    public function getConstraints(): array
    {
        return [
            'isCompany' => RuleConstraints::bool(true),
        ];
    }

    public function getName(): string
    {
        return 'customerIsCompany';
    }
}
