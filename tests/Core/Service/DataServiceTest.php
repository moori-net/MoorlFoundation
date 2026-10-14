<?php declare(strict_types=1);

namespace MoorlFoundation\Tests\Core\Service;

require_once __DIR__ . '/../../../src/Core/System/DataInterface.php';
require_once __DIR__ . '/../../../src/Core/Service/DataService.php';

use MoorlFoundation\Core\Service\DataService;
use MoorlFoundation\Core\System\DataInterface;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Defaults;

class DataServiceTest extends TestCase
{
    public function testAddsEnglishWarningToEveryMailTemplateTranslation(): void
    {
        $data = [[
            'id' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'translations' => [
                Defaults::LANGUAGE_SYSTEM => ['description' => 'Custom German description'],
                'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb' => ['description' => 'Custom English description'],
            ],
        ]];

        $service = (new \ReflectionClass(DataService::class))->newInstanceWithoutConstructor();
        $service->enrichData($data, 'mail_template', $this->dataObject());

        $warning = 'WARNING: AUTOMATICALLY GENERATED TEMPLATE! This template was generated automatically and must not be edited or overwritten directly! Please create a copy of the template before making any modifications and apply all changes exclusively to that copy. Any changes made to the original may be overwritten without warning during the next automatic generation!';

        self::assertSame($warning, $data[0]['translations'][Defaults::LANGUAGE_SYSTEM]['description']);
        self::assertSame($warning, $data[0]['translations']['bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb']['description']);
    }

    private function dataObject(): DataInterface
    {
        $dataObject = $this->createStub(DataInterface::class);
        $dataObject->method('getMediaProperties')->willReturn([]);
        $dataObject->method('getCreatedAt')->willReturn('2026-10-14 00:00:00.000');
        $dataObject->method('getReplacer')->willReturn('');

        return $dataObject;
    }
}
