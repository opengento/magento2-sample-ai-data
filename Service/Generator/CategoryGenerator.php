<?php


declare(strict_types=1);

namespace Opengento\SampleAiData\Service\Generator;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterfaceFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Opengento\SampleAiData\Helper\OpenAiRequest;

class CategoryGenerator
{
    private const NAME = 'name';
    private const DESCRIPTION = 'description';
    private const PROPERTIES = [
        1 => self::NAME,
        2 => self::DESCRIPTION,
    ];

    public function __construct(
        private readonly OpenAiRequest $openAiRequest,
        private readonly CategoryInterfaceFactory $categoryFactory,
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly State $state,
    ) {}

    /**
     * @throws CouldNotSaveException
     * @throws StateException
     * @throws InputException
     * @throws \JsonException
     */
    public function generate(string $keywords, int $maxCount = 10, int $descriptionLength = 100): void
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

        $categories = $this->openAiRequest->send($prompt);

        foreach ($categories as $category) {
            $this->createCategories($category);
        }
    }

    /**
     * @throws StateException
     * @throws CouldNotSaveException
     * @throws InputException
     */
    private function createCategories($category): void
    {
        try {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        } catch (\Exception) {}

        $newCategory = $this->categoryFactory->create();
        foreach (self::PROPERTIES as $position => $property) {
            $newCategory->setData($property, $category[$position]);
        }

        $newCategory->setIsActive(true);

        $this->categoryRepository->save($newCategory);
    }
}
