<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Cms;

use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Cms\SalesChannel\Struct\TextStruct;

class TocCmsElementResolver extends LegacyTextCmsElementResolver
{
    public function getType(): string
    {
        return 'moorl-toc';
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        parent::enrich($slot, $resolverContext, $result);

        /** @var TextStruct $text */
        $text = $slot->getData();
        if (empty($text->getContent())) {
            return;
        }

        $doc = new \DOMDocument('1.0', 'UTF-8');
        \libxml_use_internal_errors(TRUE);
        //$doc->loadHTML($text->getContent());
        $doc->loadHTML(mb_encode_numericentity($text->getContent(), [0x80, 0x10FFFF, 0, 0xFFFFFF], 'UTF-8'));
        \libxml_clear_errors();
        $xPath = new \DOMXPath($doc);

        $tableOfContents = $doc->createElement('ol');
        $tableOfContents->setAttribute('class', '');

        $allHeadings = $xPath->query('//h2|//h3|//h4|//h5|//h6');
        $headingStack = [];
        $headingCount = 0;

        foreach ($allHeadings as $heading) {
            $headingId = trim($heading->getAttribute('id'));
            $headingText = $this->getHeadingText($heading);

            if ($headingId === '' || $headingText === '') {
                continue;
            }

            $headingCount++;
            $headingDepth = $this->getHeadingDepth($heading);

            while ($headingStack !== [] && $headingDepth <= end($headingStack)->depth) {
                array_pop($headingStack);
            }

            $currentOL = $tableOfContents;
            if ($headingStack !== []) {
                $parentHeading = end($headingStack);
                if ($parentHeading->children === null) {
                    $parentHeading->children = $doc->createElement('ol');
                    $parentHeading->listItem->appendChild($parentHeading->children);
                }

                $currentOL = $parentHeading->children;
            }

            $currentOL->setAttribute('class', 'toc-lvl-' . ($headingDepth - 1));

            $currentLI = $doc->createElement('li');
            $currentAnchorLink = $doc->createElement('a');
            $currentAnchorLink->textContent = $headingText;

            $currentAnchorLink->setAttribute('href', '#' . $headingId);

            $currentLI->appendChild($currentAnchorLink);
            $currentOL->appendChild($currentLI);

            $headingStack[] = (object) [
                'depth' => $headingDepth,
                'listItem' => $currentLI,
                'children' => null,
            ];
        }

        if ($headingCount === 0) {
            $text->setContent("<!-- No valid H2, H3, H4 tags with id attribute found -->");
            return;
        }

        $text->setContent($doc->saveHTML($tableOfContents));
    }

    private function getHeadingDepth(\DOMElement $heading): int
    {
        return intval(substr($heading->tagName, 1));
    }

    private function getHeadingText(\DOMElement $heading): string
    {
        return preg_replace('/^[\s\p{Z}]+|[\s\p{Z}]+$/u', '', $heading->textContent) ?? '';
    }
}
