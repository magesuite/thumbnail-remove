<?php

namespace MageSuite\ThumbnailRemove\Test\Integration\Service;

class ThumbnailRemoverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * @var \MageSuite\ThumbnailRemove\Service\ThumbnailRemover $thumbnailRemover
     */
    private $thumbnailRemover;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->thumbnailRemover = $this->objectManager->create(\MageSuite\ThumbnailRemove\Service\ThumbnailRemover::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/dev/lazy_resize/token_secret f8f9fb44b4d7c6fe7ecef7091d475170
     * @magentoConfigFixture default/images/url_generation/include_image_file_size_in_url 1
     * @magentoDataFixture Magento/Catalog/_files/product_with_image.php
     */
    public function testRemovingImagesByProductSku()
    {
        $this->expectNotToPerformAssertions();

        $images = $this->thumbnailRemover->findImagesByProductSku('simple');
        $expected = 'catalog/product/thumbnail/[a-zA-Z0-9]+/image/13353/30x20/110/80/m/a/magento_image.jpg';
        $expected = str_replace('/', '\/', $expected);
        $expected = '/' . $expected . '/si';
        $this->assertContainsByRegex($expected, $images);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/images/url_generation/include_image_file_size_in_url 1
     * @magentoConfigFixture default/dev/lazy_resize/token_secret f8f9fb44b4d7c6fe7ecef7091d475170
     */
    public function testRemovingImagesByFileName()
    {
        $this->expectNotToPerformAssertions();

        $images = $this->thumbnailRemover->findImagesByFileName('magento_image.jpg');
        $expected = 'catalog/product/thumbnail/[a-zA-Z0-9]+/image/[0-9]+/30x20/110/80/m/a/magento_image.jpg';
        $expected = str_replace('/', '\/', $expected);
        $expected = '/' . $expected . '/si';
        $this->assertContainsByRegex($expected, $images);
    }

    protected function assertContainsByRegex($expected, $array) {
        foreach($array as $element) {
            if(preg_match($expected, $element)) {
                return true;
            }
        }

        $this->fail();
    }

}
