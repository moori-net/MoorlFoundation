<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

use Shopware\Core\Content\Media\MediaEntity;

trait EntityAvatarTrait
{
    protected ?string $avatarId = null;
    protected ?MediaEntity $avatar = null;

    public function getAvatarId(): ?string
    {
        return $this->avatarId;
    }

    public function setAvatarId(?string $avatarId): void
    {
        $this->avatarId = $avatarId;
    }

    public function getAvatar(): ?MediaEntity
    {
        return $this->avatar;
    }

    public function setAvatar(?MediaEntity $avatar): void
    {
        $this->avatar = $avatar;
    }
}
