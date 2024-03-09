<?php


declare(strict_types=1);

namespace Opengento\SampleAiData\Service\Generator;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\CategoryLinkInterface;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Opengento\SampleAiData\Service\OpenAI\Client as OpenAiClient;

class ProductGenerator
{
    public function __construct(
        private readonly OpenAiClient $openAiClient,
        private readonly ImageGenerator $imageGenerator,
        private readonly ProductFactory $productFactory,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly State $state,
        private readonly CategoryLinkManagementInterface $categoryLinkManagement
    ) {}

    /**
     * @throws CouldNotSaveException
     * @throws StateException
     * @throws InputException
     * @throws \JsonException
     */
    public function generate(string $keywords, int $maxCount = 10, int $descriptionLength = 100, CategoryInterface $category = null): void
    {
        $prompt = 'Create a list of demo products with these properties, separated values with ";". Only write down values and no property names ' . PHP_EOL;
        $prompt .= PHP_EOL;
        $prompt .= 'the following properties should be generated.' . PHP_EOL;
        $prompt .= 'Every resulting line should be in the order and sort provided below:' . PHP_EOL;
        $prompt .= PHP_EOL;
        $prompt .= 'product count.' . PHP_EOL;
        $prompt .= 'product number code. should be 16 unique random alphanumeric.' . PHP_EOL;
        $prompt .= 'name of the product must be unique.' . PHP_EOL;
        $prompt .= 'description (about ' . $descriptionLength . ' characters).' . PHP_EOL;
        $prompt .= 'price value (no currency just number).' . PHP_EOL;
        $prompt .= 'EAN code.' . PHP_EOL;
        $prompt .= 'SEO description (max 100 characters).' . PHP_EOL;
        $prompt .= 'A prompt for dall-e-2 to create an e-commerce image for the product.' . PHP_EOL;
        $prompt .= 'A prompt for dall-e-2 to create a secondary e-commerce image for the product gallery where the product is put into situation.' . PHP_EOL;
        $prompt .= PHP_EOL;
        $prompt .= 'Please only create this number of products: ' . $maxCount . PHP_EOL;
        $prompt .= PHP_EOL;
        $prompt .= 'The industry of the products should be: ' . $keywords;

        if (null !== $category) {
            $prompt .= ' and the category to which the product belongs is ' . $category->getName();
        }

        $products = $this->openAiClient->getResults($prompt);

        foreach ($products as $productData) {

            try {
                $this->createProduct(
                    $productData[1],
                    $productData[2],
                    $productData[3],
                    $productData[4],
                    $productData[5],
                    $productData[6],
                    $productData[7],
                    $productData[8],
                    $category
                );
            } catch (\Exception $e) {
                continue;
            }
        }
    }

    /**
     * @throws StateException
     * @throws CouldNotSaveException
     * @throws InputException
     */
    private function createProduct($sku, $name, $desc, $price, $ean, $shortDesc, $thumbPrompt, $galleryPrompt, $category): void
    {
        try {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        } catch (\Exception) {}

        $product = $this->productFactory->create();
        $product->setData('sku', $sku);
        $product->setData('name', $name);
        $product->setData('url_key', $name . '-' .$sku);
        $product->setData('description', $desc);
        $product->setData('price', $price);
        $product->setData('ean', $ean);
        $product->setData('short_description', $shortDesc);
        $product->setData('thumbnail', $thumbPrompt);
        $product->setData('gallery', $galleryPrompt);

        $product->setData('product_type', 'simple');
        $product->setData('attribute_set_id', '4');

        // Generate the image for the given product
        try {
            $this->imageGenerator->generateImageForProduct($product);
            $this->imageGenerator->generateImageForProduct($product, 'gallery');
        } catch (\Exception $e) {
            // Do nothing
        }

        $this->productRepository->save($product);

        // Set the category of the product
        if (null !== $category) {
            $this->categoryLinkManagement->assignProductToCategories($product->getSku(), [$category->getId()]);
        }
    }
}
