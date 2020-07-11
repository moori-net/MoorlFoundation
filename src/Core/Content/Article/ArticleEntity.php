<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Article;

use DateTimeImmutable;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ArticleEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string|null
     */
    protected $mediaUrl;
    /**
     * @var string|null
     */
    protected $articleUrl;
    /**
     * @var string|null
     */
    protected $author;
    /**
     * @var string|null
     */
    protected $title;
    /**
     * @var string|null
     */
    protected $teaser;
    /**
     * @var string|null
     */
    protected $content;
    /**
     * @var bool|null
     */
    protected $hasSeen;
    /**
     * @var DateTimeImmutable|null
     */
    protected $date;

    /**
     * @return string|null
     */
    public function getMediaUrl(): ?string
    {
        return $this->mediaUrl;
    }

    /**
     * @param string|null $mediaUrl
     */
    public function setMediaUrl(?string $mediaUrl): void
    {
        $this->mediaUrl = $mediaUrl;
    }

    /**
     * @return string|null
     */
    public function getArticleUrl(): ?string
    {
        return $this->articleUrl;
    }

    /**
     * @param string|null $articleUrl
     */
    public function setArticleUrl(?string $articleUrl): void
    {
        $this->articleUrl = $articleUrl;
    }

    /**
     * @return string|null
     */
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * @param string|null $author
     */
    public function setAuthor(?string $author): void
    {
        $this->author = $author;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string|null
     */
    public function getTeaser(): ?string
    {
        return $this->teaser;
    }

    /**
     * @param string|null $teaser
     */
    public function setTeaser(?string $teaser): void
    {
        $this->teaser = $teaser;
    }

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string|null $content
     */
    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return bool|null
     */
    public function getHasSeen(): ?bool
    {
        return $this->hasSeen;
    }

    /**
     * @param bool|null $hasSeen
     */
    public function setHasSeen(?bool $hasSeen): void
    {
        $this->hasSeen = $hasSeen;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getDate(): ?DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @param DateTimeImmutable|null $date
     */
    public function setDate(?DateTimeImmutable $date): void
    {
        $this->date = $date;
    }
}
