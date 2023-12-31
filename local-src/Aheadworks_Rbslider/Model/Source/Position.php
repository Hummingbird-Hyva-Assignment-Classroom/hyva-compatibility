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
namespace Aheadworks\Rbslider\Model\Source;

/**
 * Class Position
 * @package Aheadworks\Rbslider\Model\Source
 */
class Position implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Position values
     */
    const MENU_TOP = 1;
    const MENU_BOTTOM = 2;
    const CONTENT_TOP = 3;
    const PAGE_BOTTOM = 4;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::MENU_TOP,  'label' => __('Menu Top')],
            ['value' => self::MENU_BOTTOM,  'label' => __('Menu Bottom')],
            ['value' => self::CONTENT_TOP,  'label' => __('Content top')],
            ['value' => self::PAGE_BOTTOM,  'label' => __('Page bottom')]
        ];
    }
}
