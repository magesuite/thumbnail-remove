<?php

namespace MageSuite\ThumbnailRemove\Service;

use Magento\Framework\App\ObjectManager;
use Magento\Store\Api\StoreManagementInterface;

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
        \Magento\Catalog\Model\ProductFactory $productFactory
    )
    {
        $this->productRepository = $productRepository;
        $this->viewConfig = $viewConfig;
        $this->themeCollection = $themeCollection;
        $this->imageHelper = $imageHelper;
        $this->productFactory = $productFactory;
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

        $this->findImagesByProduct($product);

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
        $product = $this->productFactory->create();
        $fileName = sprintf('/%s/%s/%s', $name[0], $name[1], $name);

        foreach ($this->getData() as $imageData) {
            $this->processImageData($product, $imageData, $fileName);
            $imageUrl = $this->imageHelper->getUrl();
            $imagePath = $this->getImageFilePath($imageUrl);
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
                $this->processImageData($product, $imageData, $image->getFile());
                $images[] = $this->imageHelper->getUrl();
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
     * Process image data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $imageData
     * @param string $file
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function processImageData(\Magento\Catalog\Model\Product $product, array $imageData, $file)
    {
        $this->imageHelper->init($product, $imageData['id'], $imageData);
        $this->imageHelper->setImageFile($file);

        if (isset($imageData['aspect_ratio'])) {
            $this->imageHelper->keepAspectRatio($imageData['aspect_ratio']);
        }
        if (isset($imageData['frame'])) {
            $this->imageHelper->keepFrame($imageData['frame']);
        }
        if (isset($imageData['transparency'])) {
            $this->imageHelper->keepTransparency($imageData['transparency']);
        }
        if (isset($imageData['constrain'])) {
            $this->imageHelper->constrainOnly($imageData['constrain']);
        }
        if (isset($imageData['background'])) {
            $this->imageHelper->backgroundColor($imageData['background']);
        }

        return $this;
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
}