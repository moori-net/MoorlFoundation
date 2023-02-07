<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\FieldSerializer;

use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopware\Core\Framework\DataAbstractionLayer\FieldSerializer\LongTextFieldSerializer;
use Shopware\Core\Framework\DataAbstractionLayer\Write\DataStack\KeyValuePair;
use Shopware\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use Shopware\Core\Framework\Util\HtmlSanitizer;

class TocLongTextFieldSerializer extends LongTextFieldSerializer
{
    protected function sanitize(HtmlSanitizer $sanitizer, KeyValuePair $data, Field $field, EntityExistence $existence): ?string
    {
        $content = parent::sanitize($sanitizer, $data, $field, $existence);

        return $content;
    }
}
