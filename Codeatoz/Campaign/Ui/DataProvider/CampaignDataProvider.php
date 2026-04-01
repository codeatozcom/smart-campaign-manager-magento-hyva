<?php

declare(strict_types=1);

namespace Codeatoz\Campaign\Ui\DataProvider;

use Codeatoz\Campaign\Model\ResourceModel\Campaign\Collection;
use Codeatoz\Campaign\Model\ResourceModel\Campaign\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Grid listing data provider.
 * Returns raw collection rows — no config_json flattening (handled by form provider).
 */
class CampaignDataProvider extends AbstractDataProvider
{
    /** @var Collection */
    protected $collection;

    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData(): array
    {
        $items = [];
        foreach ($this->collection->getItems() as $campaign) {
            $items[] = $campaign->getData();
        }

        return [
            'totalRecords' => $this->collection->getSize(),
            'items'        => $items,
        ];
    }
}
