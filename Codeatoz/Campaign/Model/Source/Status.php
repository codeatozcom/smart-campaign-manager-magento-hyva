<?php

declare(strict_types=1);

namespace Codeatoz\Campaign\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    public const DRAFT     = 0;
    public const SCHEDULED = 1;
    public const ACTIVE    = 2;
    public const EXPIRED   = 3;

    public function toOptionArray(): array
    {
        return [
            ['value' => self::DRAFT,     'label' => __('Draft')],
            ['value' => self::SCHEDULED, 'label' => __('Scheduled')],
            ['value' => self::ACTIVE,    'label' => __('Active')],
            ['value' => self::EXPIRED,   'label' => __('Expired')],
        ];
    }

    public function toArray(): array
    {
        return [
            self::DRAFT     => __('Draft'),
            self::SCHEDULED => __('Scheduled'),
            self::ACTIVE    => __('Active'),
            self::EXPIRED   => __('Expired'),
        ];
    }
}
