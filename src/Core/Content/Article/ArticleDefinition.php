<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Article;

use MoorlMagazine\Core\Content\Aggregate\ArticleProduct\ArticleProductDefinition;
use MoorlMagazine\Core\Content\Aggregate\ArticleTag\ArticleTagDefinition;
use MoorlMagazine\Core\Content\Aggregate\ArticleCategory\ArticleCategoryDefinition;
use MoorlMagazine\Core\Content\Category\CategoryDefinition;
use MoorlMagazine\Core\Content\Comment\CommentDefinition;
use Shopware\Core\Content\Cms\CmsPageDefinition;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\AllowHtml;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\WriteProtected;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use MoorlMagazine\Core\Content\Aggregate\ArticleTranslation\ArticleTranslationDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;
use Shopware\Core\System\Tag\TagDefinition;
use Shopware\Core\System\User\UserDefinition;

class ArticleDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'moorl_foundation_article';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ArticleEntity::class;
    }

    public function getCollectionClass(): string
    {
        return ArticleCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            new StringField('media_url', 'mediaUrl'),
            new StringField('article_url', 'articleUrl'),
            new StringField('author', 'author'),
            new StringField('title', 'title'),
            new StringField('teaser', 'teaser'),
            (new LongTextField('content', 'content'))->addFlags(new Required())->addFlags(new AllowHtml()),
            new BoolField('has_seen', 'hasSeen'),
            (new DateField('date', 'date'))->addFlags(new Required()),
        ]);
    }
}
