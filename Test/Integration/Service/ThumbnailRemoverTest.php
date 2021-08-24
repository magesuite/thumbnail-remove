<?php

namespace MageSuite\ThumbnailRemove\Test\Integration\Service;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */

class ThumbnailRemoverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var MageSuite\ThumbnailRemove\Service\ThumbnailRemover $thumbnailRemover
     */
    protected $thumbnailRemover;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->thumbnailRemover = $this->objectManager->create('MageSuite\ThumbnailRemove\Service\ThumbnailRemover');
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/dev/lazy_resize/token_secret f8f9fb44b4d7c6fe7ecef7091d475170
     * @magentoDataFixture Magento/Catalog/_files/product_with_image.php
     */
    public function testRemovingImagesByProductSku()
    {
        $images = $this->thumbnailRemover->findImagesByProductSku('simple');
        $expected = BP . '/pub/media/catalog/product/thumbnail/b67a60baa74918b514f70983b0a8e7140775c7c31b73fb08bfcd5154/image/13353/30x20/110/0/m/a/magento_image.jpg';
        $expected = str_replace('/', DIRECTORY_SEPARATOR, $expected);
        $this->assertContains($expected, $images);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/dev/lazy_resize/token_secret f8f9fb44b4d7c6fe7ecef7091d475170
     */
    public function testRemovingImagesByFileName()
    {
        $images = $this->thumbnailRemover->findImagesByFileName('magento_image.jpg');
        $expected = BP . '/pub/media/catalog/product/thumbnail/44a72fa20f436a2bb4cf5b065283f7eb304b6f26123bcd35c9147740/image/0/30x20/110/0/m/a/magento_image.jpg';
        $expected = str_replace('/', DIRECTORY_SEPARATOR, $expected);
        $this->assertContains($expected, $images);
    }
}
