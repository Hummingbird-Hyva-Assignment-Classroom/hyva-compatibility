<?php
/**
 * Aheadworks Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://aheadworks.com/end-user-license-agreement/
 *
 * @package    Rbslider
 * @version    1.4.5
 * @copyright  Copyright (c) 2022 Aheadworks Inc. (https://aheadworks.com/)
 * @license    https://aheadworks.com/end-user-license-agreement/
 */
namespace Aheadworks\Rbslider\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\Filter\FilterManager;

/**
 * Class Name
 * @package Aheadworks\Rbslider\Ui\Component\Listing\Columns
 */
class Name extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var FilterManager
     */
    private $filterManager;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param FilterManager $filterManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        FilterManager $filterManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->filterManager = $filterManager;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        foreach ($dataSource['data']['items'] as &$item) {
            if ($this->getName() == 'slide_name') {
                $item['slide_name_url'] = $this->getLink($item['slide_id'], 'slide');
            } elseif ($this->getName() == 'banner_name') {
                $item['banner_name_url'] = $this->getLink($item['banner_id'], 'banner');
            } else {
                $itemConfig = $this->getConfig();
                $item['name_url'] = $this->getLink($item['id'], $itemConfig['type']);
            }
        }

        return $dataSource;
    }

    /**
     * Retrieve link for entity
     *
     * @param int $entityId
     * @param string $type
     * @return string
     */
    private function getLink($entityId, $type)
    {
        return $this->context->getUrl('aw_rbslider_admin/'.$type.'/edit', ['id' => $entityId]);
    }
}
