<?php

declare(strict_types=1);

namespace Opengento\SampleAiData\Service\Generator;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverPool;
use Opengento\SampleAiData\Service\OpenAI\Client as OpenAiClient;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Filesystem\Directory\Write;

class ImageGenerator
{
    private const UPLOAD_DIR_NAME = 'catalog_uploads';

    private Write $uploadsDir;

    public function __construct(
        private readonly OpenAiClient $openAiClient,
        private readonly WriteFactory $writeFactory,
        Filesystem $filesystem
    ) {
        $mediaDir = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);

        // Make the uploads dire if it doesn't exist
        if (!$mediaDir->isDirectory(self::UPLOAD_DIR_NAME)) {
            $mediaDir->create(self::UPLOAD_DIR_NAME);
        }

        $this->uploadsDir = $this->writeFactory->create($mediaDir->getAbsolutePath(self::UPLOAD_DIR_NAME), DriverPool::FILE);
    }

    public function generateImageForProduct(ProductInterface $product, $imageType = 'thumbnail')
    {
        // Generate the image for the given product
        if ($product->hasData($imageType)) {
            $imagePrompt = $product->getData($imageType);
        } else {
            $imagePrompt = 'Generate a ' . $imageType . ' image for a product named ' . $product->getName() . '. The image has to be used on an e-commerce website';
        }

        $imageUrl = $this->openAiClient->generateImage($imagePrompt);

        $imageFileName = $product->getSku() . '.png';
        $imageFilePath = $this->uploadsDir->getAbsolutePath($imageFileName);

        $ch = curl_init($imageUrl);

        /* @phpstan-ignore-next-line */
        $fp = fopen($imageFilePath, 'wb');
        /* @phpstan-ignore-next-line */
        curl_setopt($ch, CURLOPT_FILE, $fp);
        /* @phpstan-ignore-next-line */
        curl_setopt($ch, CURLOPT_HEADER, 0);
        /* @phpstan-ignore-next-line */
        curl_exec($ch);
        /* @phpstan-ignore-next-line */
        curl_close($ch);
        /* @phpstan-ignore-next-line */
        fclose($fp);

        if ($imageType === 'thumbnail') {
            $mediaAttribute = ['image', 'small_image', 'thumbnail'];
        } else {
            $mediaAttribute = [];
        }

        $product->addImageToMediaGallery($imageFilePath, $mediaAttribute, true, false);
    }
}
