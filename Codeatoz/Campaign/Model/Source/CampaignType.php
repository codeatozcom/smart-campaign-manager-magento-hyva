<?php

declare(strict_types=1);

namespace Codeatoz\Campaign\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CampaignType implements OptionSourceInterface
{
    public const GENERAL   = 'general';
    public const FLASH     = 'flash_sale';
    public const WEEKEND   = 'weekend_deal';
    public const CLEARANCE = 'clearance';
    public const SEASONAL  = 'seasonal';
    public const LAUNCH    = 'launch';

    public function toOptionArray(): array
    {
        return [
            ['value' => self::GENERAL,   'label' => __('General')],
            ['value' => self::FLASH,     'label' => __('Flash Sale')],
            ['value' => self::WEEKEND,   'label' => __('Weekend Deal')],
            ['value' => self::CLEARANCE, 'label' => __('Clearance')],
            ['value' => self::SEASONAL,  'label' => __('Seasonal')],
            ['value' => self::LAUNCH,    'label' => __('Product Launch')],
        ];
    }

    public function toArray(): array
    {
        return [
            self::GENERAL   => __('General'),
            self::FLASH     => __('Flash Sale'),
            self::WEEKEND   => __('Weekend Deal'),
            self::CLEARANCE => __('Clearance'),
            self::SEASONAL  => __('Seasonal'),
            self::LAUNCH    => __('Product Launch'),
        ];
    }
}
