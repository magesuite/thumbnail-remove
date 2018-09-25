<?php

namespace MageSuite\ThumbnailRemove\Api;

class ThumbnailRemove implements ThumbnailRemoveInterface
{
    /**
     * @var \MageSuite\ThumbnailRemove\Model\ThumbnailRemover $thumbnailRemover
     */
    protected $thumbnailRemover;

    /**
     * @param \MageSuite\ThumbnailRemove\Model\ThumbnailRemover $thumbnailRemover
     */
    public function __construct(\MageSuite\ThumbnailRemove\Service\ThumbnailRemover $thumbnailRemover)
    {
        $this->thumbnailRemover = $thumbnailRemover;
    }

    /**
     * @inheritdoc
     */
    public function removeBySku($sku)
    {
        $result = $this->thumbnailRemover->removeByProductSku($sku);

        return json_encode($result);
    }

    /**
     * @inheritdoc
     */
    public function removeByName($name)
    {
        $result = $this->thumbnailRemover->removeByImageFileName($name);

        return json_encode($result);
    }
}