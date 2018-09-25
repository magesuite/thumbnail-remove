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
    private $objectManager;

    /**
     * @var MageSuite\ThumbnailRemove\Service\ThumbnailRemover $thumbnailRemover
     */
    private $thumbnailRemover;

    public function setUp()
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
        $expected = BP . '/pub/media/catalog/product/thumbnail/0317a6db24934ab7b3d0d25e4d389f49/swatch_image/30x20/000/80/m/a/magento_image.jpg';
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
        $expected = BP . '/pub/media/catalog/product/thumbnail/0317a6db24934ab7b3d0d25e4d389f49/swatch_image/30x20/000/80/m/a/magento_image.jpg';
        $expected = str_replace('/', DIRECTORY_SEPARATOR, $expected);
        $this->assertContains($expected, $images);
    }

}