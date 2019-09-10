<?php

namespace H4w\Datafrete\Model\Config\Source;

class Sizeunit implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [
            ['value' => 'cm', 'label' => 'cm'],
            ['value' => 'm',  'label' => 'm'],
        ];
    }

}