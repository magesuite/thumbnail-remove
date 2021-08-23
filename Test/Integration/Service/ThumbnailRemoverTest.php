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
     * @magentoDataFixture Magento/Catalog/_files/product_with_image.php
     */
    public function testRemovingImagesByProductSku()
    {
        $images = $this->thumbnailRemover->findImagesByProductSku('simple');
        $expected = BP . '/pub/media/catalog/product/thumbnail/7e3caa6f01a6d83d364c562ba0c5d9f4ab80134e2336e76666b83b4a/image/13353/30x20/110/0/m/a/magento_image.jpg';
        $expected = str_replace('/', DIRECTORY_SEPARATOR, $expected);
        $this->assertContains($expected, $images);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testRemovingImagesByFileName()
    {
        $images = $this->thumbnailRemover->findImagesByFileName('magento_image.jpg');
        $expected = BP . '/pub/media/catalog/product/thumbnail/f8e59831fbf481c3182c35a778a72db57fe330c4431776c50974c469/image/0/30x20/110/0/m/a/magento_image.jpg';
        $expected = str_replace('/', DIRECTORY_SEPARATOR, $expected);
        $this->assertContains($expected, $images);
    }
}
