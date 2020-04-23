<?php

namespace MageSuite\ThumbnailRemove\Service;

use Magento\Framework\App\ObjectManager;

class ThumbnailRemover
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ConfigInterface
     */
    protected $viewConfig;

    /**
     * @var ThemeCollection
     */
    protected $themeCollection;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @var ProductFactory $productFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Image\ParamsBuilder
     */
    protected $imageParamsBuilder;


    /**
     * @var \Magento\Catalog\Model\View\Asset\ImageFactory
     */
    protected $assetImageFactory;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\View\ConfigInterface $viewConfig,
        \Magento\Theme\Model\ResourceModel\Theme\Collection $themeCollection,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Image\ParamsBuilder $imageParamsBuilder,
        \Magento\Catalog\Model\View\Asset\ImageFactory $assetImageFactory
    )
    {
        $this->productRepository = $productRepository;
        $this->viewConfig = $viewConfig;
        $this->themeCollection = $themeCollection;
        $this->imageHelper = $imageHelper;
        $this->productFactory = $productFactory;
        $this->imageParamsBuilder = $imageParamsBuilder;
        $this->assetImageFactory = $assetImageFactory;
    }

    /**
     * @param string $sku
     */
    public function removeByProductSku($sku)
    {
        $images = $this->findImagesByProductSku($sku);
        $this->removeImages($images);
    }

    /**
     * @param string $name
     */
    public function removeByImageFileName($name)
    {
        $images = $this->findImagesByFileName($name);
        $this->removeImages($images);
    }

    /**
     * @param string $sku
     * @return string[]
     */
    public function findImagesByProductSku($sku)
    {
        $images = [];

        $product = $this->productRepository->get($sku, false, ObjectManager::getInstance()->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore()->getId(), true);
        foreach ($this->findImagesByProduct($product) as $imageUrl) {
            $imagePath = $this->getImageFilePath($imageUrl);
            $images[] = $imagePath;
        }
        return $images;
    }

    /**
     * @param string $name
     * @return string[]
     */
    public function findImagesByFileName($name)
    {
        $images = [];
        $fileName = sprintf('/%s/%s/%s', $name[0], $name[1], $name);

        foreach ($this->getData() as $imageData) {
            $assetImage = $this->getAssetImage($fileName, $imageData);
            $imagePath = $this->getImageFilePath($assetImage->getUrl());
            $images[] = $imagePath;
        }
        return $images;
    }

    /**
     * @param array $images
     */
    private function removeImages(array $images)
    {
        foreach ($images as $image) {
            @unlink($image);
        }
    }

    /**
     * Find all possible images paths
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string[]
     */
    private function findImagesByProduct(\Magento\Catalog\Model\Product $product)
    {
        $images = [];
        $galleryImages = $product->getMediaGalleryImages();
        foreach ($galleryImages as $image) {
            foreach ($this->getData() as $imageData) {
                $assetImage = $this->getAssetImage($image->getFile(), $imageData);
                $images[] = $assetImage->getUrl();
            }
        }
        return $images;
    }

    /**
     * Retrieve view configuration data
     *
     * Collect data for 'Magento_Catalog' module from /etc/view.xml files.
     *
     * @return array
     */
    protected function getData()
    {
        if (!$this->data) {
            /** @var \Magento\Theme\Model\Theme $theme */
            foreach ($this->themeCollection->loadRegisteredThemes() as $theme) {
                $config = $this->viewConfig->getViewConfig([
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'themeModel' => $theme,
                ]);
                $images = $config->getMediaEntities('Magento_Catalog', \Magento\Catalog\Helper\Image::MEDIA_TYPE_CONFIG_NODE);
                foreach ($images as $imageId => $imageData) {
                    $this->data[$theme->getCode() . $imageId] = array_merge(['id' => $imageId], $imageData);
                }
            }
        }
        return $this->data;
    }

    /**
     * @param $imageUrl
     * @return string
     */
    protected function getImageFilePath($imageUrl)
    {
        preg_match_all('/catalog\/product\/(.*)/si', $imageUrl, $results, PREG_SET_ORDER);

        $imagePath = sprintf('%s/pub/media/catalog/product/%s', BP, $results[0][1]);

        return $imagePath;
    }

    protected function getAssetImage($filename, $imageData)
    {
        $imageMiscParams = $this->imageParamsBuilder->build($imageData);
        return $this->assetImageFactory->create(
            [
                'miscParams' => $imageMiscParams,
                'filePath' => $filename,
            ]
        );
    }
}