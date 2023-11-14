<?php

namespace MageSuite\ThumbnailRemove\Api;

interface ThumbnailRemoveInterface {

    /**
     * @api
     * @param string $sku
     * @return string
     */
    public function removeBySku($sku);

    /**
     * @api
     * @param string $name
     * @return string
     */
    public function removeByName($name);
}
