<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Cms;

use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\TextCmsElementResolver;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Cms\SalesChannel\Struct\TextStruct;

class TocCmsElementResolver extends LegacyTextCmsElementResolver
{
    public function getType(): string
    {
        return 'moorl-toc';
    }

    /**
     * @param CmsSlotEntity $slot
     * @param ResolverContext $resolverContext
     * @param ElementDataCollection $result
     *
     * @link: https://www.terluinwebdesign.nl/en/html/html-table-of-contents-generator-php-powered/
     */
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
        $doc->loadHTML(mb_convert_encoding($text->getContent(), 'HTML-ENTITIES', 'UTF-8'));
        \libxml_clear_errors();
        $xPath = new \DOMXPath($doc);

        $tableOfContents = $doc->createElement('ol');
        $tableOfContents->setAttribute('class', '');

        $currentOL = $tableOfContents;
        $previousOLs = [];

        $previousHeading = false;
        $allHeadings = $xPath->query('//h2|//h3|//h4|//h5|//h6');

        $iHeading = 0;

        $previousLI = false;

        foreach ($allHeadings as $heading) {
            if (!$heading->hasAttribute('id')) {
                continue;
            }

            $iHeading++;
            $headingDepth = $this->getHeadingDepth($heading);

            if ($previousHeading) {
                $previousHeadingDepth = $this->getHeadingDepth($previousHeading);
            } else {
                $previousHeadingDepth = $headingDepth;
            }

            if ($headingDepth > $previousHeadingDepth) {
                $previousOLs[$previousHeadingDepth] = $currentOL;
                $currentOL = $doc->createElement('ol');
                if ($previousLI) {
                    $previousLI->appendChild($currentOL);
                } else {
                    $previousOLs[$previousHeadingDepth]->appendChild($currentOL);
                }
            } elseif ($headingDepth < $previousHeadingDepth) {
                $currentOL = $previousOLs[$headingDepth];
            }

            if (!$currentOL) {
                continue;
            }

            $currentOL->setAttribute('class', 'toc-lvl-' . ($headingDepth - 1));

            $currentLI = $doc->createElement('li');
            $currentAnchorLink = $doc->createElement('a');
            $currentAnchorLink->textContent = $heading->textContent;

            $currentAnchorLink->setAttribute('href', '#' . $heading->getAttribute('id'));

            $currentLI->appendChild($currentAnchorLink);
            $currentOL->appendChild($currentLI);

            $previousLI = $currentLI;
            $previousHeading = $heading;
            $previousHeadingDepth = $headingDepth;
        }

        if ($iHeading === 0) {
            $text->setContent("<!-- No H2, H3, H4 tags with id attribute found -->");
            return;
        }

        $text->setContent($doc->saveHTML($tableOfContents));
    }

    private function getHeadingDepth(\DOMElement $heading): int
    {
        return intval(substr($heading->tagName, 1));
    }
}
