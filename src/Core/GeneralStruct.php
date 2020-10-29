<?php declare(strict_types=1);

namespace MoorlFoundation\Core;

use Shopware\Core\Framework\Struct\Struct;

// TODO: Wird entfernt sobald MoorlMerchantVoucher, MoorlMagazine Update erhält
class GeneralStruct extends Struct
{
    public function __construct(array $values = [])
    {
        foreach ($values as $name => $value) {
            $this->$name = $value;
        }
    }
}
