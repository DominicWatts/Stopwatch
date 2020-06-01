<?php
declare(strict_types=1);

namespace Xigen\Stopwatch\Console\Command;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Helper\Stock;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class Time extends Command
{
    /**
     * @var State
     */
    private $state;

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var Stock
     */
    protected $stockFilter;

    /**
     * Console Test script
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        State $state,
        CollectionFactory $productCollectionFactory,
        Stock $stockFilter
    ) {
        $this->state = $state;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->stockFilter = $stockFilter;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $this->state->setAreaCode(Area::AREA_GLOBAL);
        $output->writeln('<info>Start</info>');

        $stopwatch = new Stopwatch();
        $stopwatch->start('stopwatch');

        $collection = $this->getRandomProduct(1000, true, true);
        foreach ($collection as $item) {
            $output->writeln((string)__('<info>%1</info>', $item->getSku()));
        }

        $event = $stopwatch->stop('stopwatch');

        $output->writeln((string) $event);
        $output->writeln((string) __("Start : %1", $event->getStartTime()));
        // milliseconds to date
        $output->writeln((string) __("Start : %1", date("d-m-Y H:i:s", (int) ($event->getOrigin() / 1000))));
        $output->writeln((string) __("End : %1", $event->getEndTime()));
        // milliseconds to date
        $output->writeln((string) __("End : %1", date("d-m-Y H:i:s", (int) (($event->getOrigin() + $event->getEndTime()) / 1000))));
        $output->writeln((string) __("Memory : %1 MiB", $event->getMemory() / 1024 / 1024));
        $output->writeln('<info>Finish</info>');
    }

    /**
     * Return collection of random products.
     * @param int $limit
     * @param bool $inStockOnly
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getRandomProduct($limit = 1, $inStockOnly = false, $simpleOnly = true, $attributes = [])
    {
        $collection = $this->productCollectionFactory
            ->create()
            ->addAttributeToSelect(empty($attributes) ? '*' : $attributes)
            ->setPageSize($limit);

        if ($simpleOnly) {
            $collection->addAttributeToFilter(ProductInterface::TYPE_ID, ['eq' => Type::TYPE_SIMPLE]);
        }

        if ($inStockOnly) {
            $this->stockFilter->addInStockFilterToCollection($collection);
        }

        $collection->getSelect()->order('RAND()');

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("xigen:stopwatch:go");
        $this->setDescription("Time some actions with Symfony stopwatch");
        parent::configure();
    }
}
