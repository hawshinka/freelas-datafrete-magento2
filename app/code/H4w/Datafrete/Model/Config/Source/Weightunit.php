<?php

namespace H4w\Datafrete\Model\Config\Source;

class Weightunit implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [
            ['value' => 'kg', 'label' => 'Kg'],
            ['value' => 'g',  'label' => 'g'],
        ];
    }

}