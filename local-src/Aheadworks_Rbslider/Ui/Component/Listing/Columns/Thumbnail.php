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

use Aheadworks\Rbslider\Model\Source\ImageType;
use Aheadworks\Rbslider\Model\Slide\ImageFileUploader;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;

/**
 * Class Thumbnail
 * @package Aheadworks\Rbslider\Ui\Component\Listing\Columns
 */
class Thumbnail extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var ImageFileUploader
     */
    private $imageFileUploader;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param ImageFileUploader $imageFileUploader
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        ImageFileUploader $imageFileUploader,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
        $this->imageFileUploader = $imageFileUploader;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $imgUrl = $item['img_type'] == ImageType::TYPE_FILE
                    ? $this->imageFileUploader->getMediaUrl($item['img_file'])
                    : $item['img_url'];
                $item[$fieldName . '_src'] = $item[$fieldName . '_orig_src'] = $imgUrl;
                $id = isset($item['slide_id'])
                    ? $item['slide_id']
                    : $item['id'];
                $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                    'aw_rbslider_admin/slide/edit',
                    ['id' => $id]
                );
            }
        }
        return $dataSource;
    }
}
