<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Bundle\CoreBundle\Validator\Constraints;

use Sylius\Component\Core\Model\CatalogPromotionRuleInterface;
use Sylius\Component\Core\Repository\ProductVariantRepositoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class CatalogPromotionRuleValidator extends ConstraintValidator
{
    private ProductVariantRepositoryInterface $variantRepository;

    private RepositoryInterface $taxonRepository;

    public function __construct(
        ProductVariantRepositoryInterface $variantRepository,
        RepositoryInterface $taxonRepository
    ) {
        $this->variantRepository = $variantRepository;
        $this->taxonRepository = $taxonRepository;
    }

    public function validate($value, Constraint $constraint): void
    {
        /** @var CatalogPromotionRule $constraint */
        Assert::isInstanceOf($constraint, CatalogPromotionRule::class);

        /** @var CatalogPromotionRuleInterface $value */
        if (
            $value->getType() !== CatalogPromotionRuleInterface::TYPE_FOR_VARIANTS &&
            $value->getType() !== CatalogPromotionRuleInterface::TYPE_FOR_TAXONS
        ) {
            $this->context->buildViolation($constraint->invalidType)->atPath('type')->addViolation();

            return;
        }

        $configuration = $value->getConfiguration();

        if ($value->getType() === CatalogPromotionRuleInterface::TYPE_FOR_VARIANTS) {
            $this->validateForVariantsType($configuration, $constraint);

            return;
        }

        $this->validateForTaxonType($configuration, $constraint);
    }

    private function validateForVariantsType(array $configuration, Constraint $constraint): void
    {
        if (!array_key_exists('variants', $configuration) || empty($configuration['variants'])) {
            $this->context->buildViolation($constraint->notEmpty)->atPath('configuration.variants')->addViolation();

            return;
        }

        foreach ($configuration['variants'] as $variantCode) {
            if (null === $this->variantRepository->findOneBy(['code' => $variantCode])) {
                $this->context->buildViolation($constraint->invalidVariants)->atPath('configuration.variants')->addViolation();

                break;
            }
        }
    }

    private function validateForTaxonType(array $configuration, Constraint $constraint): void
    {
        if (!array_key_exists('taxon', $configuration) || empty($configuration['taxon']['taxonCode'])) {
            $this->context->buildViolation($constraint->notEmpty)->atPath('configuration.variants')->addViolation();

            return;
        }

        if (null === $this->taxonRepository->findOneBy(['code' => $configuration['taxon']['taxonCode']])) {
            $this->context->buildViolation($constraint->invalidVariants)->atPath('configuration.variants')->addViolation();
        }
    }
}
