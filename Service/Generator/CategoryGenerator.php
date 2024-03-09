<?php


declare(strict_types=1);

namespace Opengento\SampleAiData\Service\Generator;

use Magento\Catalog\Api\CategoryManagementInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\CategoryInterfaceFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Opengento\SampleAiData\Helper\OpenAiRequest;
use Opengento\SampleAiData\Service\OpenAI\Client;

class CategoryGenerator
{
    private const NAME = 'name';
    private const DESCRIPTION = 'description';
    private const PROPERTIES = [
        1 => self::NAME,
        2 => self::DESCRIPTION,
    ];

    /**
     * @param OpenAiRequest $openAiRequest
     * @param CategoryInterfaceFactory $categoryFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CategoryManagementInterface $categoryManagement
     * @param State $state
     */
    public function __construct(
        private readonly Client $openAiClient,
        private readonly CategoryInterfaceFactory $categoryFactory,
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly CategoryManagementInterface $categoryManagement,
        private readonly State $state,
    ) {}

    /**
     * @param string $keywords
     * @param int $maxCount
     * @param int $descriptionLength
     * @param int|null $maxSubcategoryLevel
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     */
    public function generate(string $keywords, int $maxCount = 10, int $descriptionLength = 100, int $maxSubcategoryLevel = null): void
    {
        try {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        } catch (\Exception) {}

        $this->createCategories($keywords, $maxCount, $descriptionLength);

        if ($maxSubcategoryLevel) {
            $categories = $this->categoryManagement->getTree(null, 1);
            foreach ($categories as $category) {
                $newKeywords = $keywords . ' - ' . $category->getName();
                $this->createCategories($newKeywords, $maxCount, $descriptionLength, $category);
            }
        }
    }

    /**
     * @param string $keywords
     * @param int $maxCount
     * @param int $descriptionLength
     * @param int|null $maxSubcategoryLevel
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     */
    public function createCategories(string $keywords, int $maxCount = 10, int $descriptionLength = 100, CategoryInterface $parentCategory = null): void
    {
        $prompt = 'Create a list of categories with these properties, separated values with ";". Only write down values and no property names' . PHP_EOL;
        $prompt .= PHP_EOL;
        $prompt .= 'the following properties should be generated.' . PHP_EOL;
        $prompt .= 'Every resulting line should be on one line, in the order and sort provided below:' . PHP_EOL;
        $prompt .= PHP_EOL;
        $prompt .= 'category count.' . PHP_EOL;
        $prompt .= 'name of the category.' . PHP_EOL;
        $prompt .= 'description (about ' . $descriptionLength . ' characters).' . PHP_EOL;
        $prompt .= PHP_EOL;
        $prompt .= 'Please only create this number of categories: ' . $maxCount . ' items.' . PHP_EOL;
        $prompt .= PHP_EOL;
        $prompt .= 'The industry of the categories should be: ' . $keywords;

        $categories = $this->openAiClient->getResults($prompt);

        foreach ($categories as $category) {
            $this->createCategory($category, $parentCategory);
        }
    }

    /**
     * @throws StateException
     * @throws CouldNotSaveException
     * @throws InputException
     */
    private function createCategory(array $category, CategoryInterface $parentId = null): void
    {
        /** @var CategoryInterface $newCategory */
        $newCategory = $this->categoryFactory->create();
        foreach (self::PROPERTIES as $position => $property) {
            $newCategory->setData($property, $category[$position]);
        }

        $newCategory->setIsActive(true);

        if ($parentId) {
            $newCategory->setParentId($category->getId());
        }

        $this->categoryRepository->save($newCategory);
    }
}
