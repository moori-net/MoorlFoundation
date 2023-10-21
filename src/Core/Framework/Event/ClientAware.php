<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\Event;

use MoorlFoundation\Core\Content\Client\ClientEntity;
use Shopware\Core\Framework\Event\FlowEventAware;
use Shopware\Core\Framework\Log\Package;

#[Package('business-ops')]
interface ClientAware extends FlowEventAware
{
    public const CLIENT = 'client';

    public const CLIENT_ID = 'clientId';

    public function getClient(): ClientEntity;
}
