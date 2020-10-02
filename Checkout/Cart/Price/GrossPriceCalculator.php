<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Cart\Price;

use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\ListPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\ReferencePrice;
use Shopware\Core\Checkout\Cart\Price\Struct\ReferencePriceDefinition;
use Shopware\Core\Checkout\Cart\Tax\TaxCalculator;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig;

class GrossPriceCalculator
{
    /**
     * @var TaxCalculator
     */
    private $taxCalculator;

    /**
     * @var CashRounding
     */
    private $priceRounding;

    public function __construct(
        TaxCalculator $taxCalculator,
        CashRounding $priceRounding
    ) {
        $this->taxCalculator = $taxCalculator;
        $this->priceRounding = $priceRounding;
    }

    /**
     * @deprecated tag:v6.4.0 - `$config` parameter will be required
     */
    public function calculate(QuantityPriceDefinition $definition, ?CashRoundingConfig $config = null): CalculatedPrice
    {
        // @deprecated tag:v6.4.0 - `$config` parameter will be required
        $config = $config ?? new CashRoundingConfig($definition->getPrecision(), 0.01, true);

        $unitPrice = $this->getUnitPrice($definition, $config);

        $unitTaxes = $this->taxCalculator->calculateGrossTaxes($unitPrice, $definition->getTaxRules());

        foreach ($unitTaxes as $tax) {
            $total = $this->priceRounding->mathRound(
                $tax->getTax() * $definition->getQuantity(),
                $config
            );

            $tax->setTax($total);

            $tax->setPrice($tax->getPrice() * $definition->getQuantity());
        }

        $price = $this->priceRounding->cashRound(
            $unitPrice * $definition->getQuantity(),
            $config
        );

        $reference = $this->calculateReferencePrice($price, $definition->getReferencePriceDefinition(), $config);

        return new CalculatedPrice(
            $unitPrice,
            $price,
            $unitTaxes,
            $definition->getTaxRules(),
            $definition->getQuantity(),
            $reference,
            $this->calculateListPrice($unitPrice, $definition, $config)
        );
    }

    private function getUnitPrice(QuantityPriceDefinition $definition, CashRoundingConfig $config): float
    {
        //item price already calculated?
        if ($definition->isCalculated()) {
            return $this->priceRounding->cashRound($definition->getPrice(), $config);
        }

        $price = $this->taxCalculator->calculateGross(
            $definition->getPrice(),
            $definition->getTaxRules()
        );

        return $this->priceRounding->cashRound($price, $config);
    }

    private function calculateListPrice(float $unitPrice, QuantityPriceDefinition $definition, CashRoundingConfig $config): ?ListPrice
    {
        $price = $definition->getListPrice();
        if (!$price) {
            return null;
        }

        if (!$definition->isCalculated()) {
            $price = $this->taxCalculator->calculateGross(
                $price,
                $definition->getTaxRules()
            );
        }

        $listPrice = $this->priceRounding->cashRound($price, $config);

        return ListPrice::createFromUnitPrice($unitPrice, $listPrice);
    }

    private function calculateReferencePrice(float $price, ?ReferencePriceDefinition $definition, CashRoundingConfig $config): ?ReferencePrice
    {
        if (!$definition) {
            return null;
        }

        $price = $price / $definition->getPurchaseUnit() * $definition->getReferenceUnit();

        $price = $this->priceRounding->mathRound($price, $config);

        return new ReferencePrice(
            $price,
            $definition->getPurchaseUnit(),
            $definition->getReferenceUnit(),
            $definition->getUnitName()
        );
    }
}
