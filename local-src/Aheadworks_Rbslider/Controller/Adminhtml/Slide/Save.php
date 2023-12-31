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
declare(strict_types=1);

namespace Aheadworks\Rbslider\Controller\Adminhtml\Slide;

use Aheadworks\Rbslider\Api\Data\SlideInterface;
use Aheadworks\Rbslider\Api\Data\SlideInterfaceFactory;
use Aheadworks\Rbslider\Api\SlideRepositoryInterface;
use Aheadworks\Rbslider\Model\Source\ImageType;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime as StdlibDateTime;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Save
 * @package Aheadworks\Rbslider\Controller\Adminhtml\Slide
 */
class Save extends Action implements HttpPostActionInterface
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Rbslider::slides';

    /**
     * @var SlideRepositoryInterface
     */
    private $slideRepository;

    /**
     * @var SlideInterfaceFactory
     */
    private $slideDataFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @param Context $context
     * @param SlideRepositoryInterface $slideRepository
     * @param SlideInterfaceFactory $slideDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataPersistorInterface $dataPersistor
     * @param StoreManagerInterface $storeManager
     * @param DateTime $dateTime
     */
    public function __construct(
        Context $context,
        SlideRepositoryInterface $slideRepository,
        SlideInterfaceFactory $slideDataFactory,
        DataObjectHelper $dataObjectHelper,
        DataPersistorInterface $dataPersistor,
        StoreManagerInterface $storeManager,
        DateTime $dateTime
    ) {
        parent::__construct($context);
        $this->slideRepository = $slideRepository;
        $this->slideDataFactory = $slideDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager;
        $this->dateTime = $dateTime;
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data = $this->getRequest()->getPostValue()) {
            try {
                $data = $this->prepareData($data);
                $this->validate($data);
                $slide = $this->performSave($data);

                $this->dataPersistor->clear('aw_rbslider_slide');
                $this->messageManager->addSuccessMessage(__('Slide was successfully saved'));
                if ($this->getRequest()->getParam('back') == 'edit') {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $slide->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the slide'));
            }
            if ($data['img_type'] == ImageType::TYPE_FILE) {
                $data['img_file'] = $data['img_file_tmp'];
                if (isset($data['mobile_img_file_tmp'])) {
                    $data['mobile_img_file'] = $data['mobile_img_file_tmp'];
                }
            }
            $this->dataPersistor->set('aw_rbslider_slide', $data);
            $id = isset($data['id']) ? $data['id'] : false;
            if ($id) {
                return $resultRedirect->setPath('*/*/edit', ['id' => $id, '_current' => true]);
            }
            return $resultRedirect->setPath('*/*/new', ['_current' => true]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Perform save
     *
     * @param array$data
     * @return SlideInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function performSave($data)
    {
        $id = isset($data['id']) ? $data['id'] : false;
        $bannerDataObject = $id
            ? $this->slideRepository->get($id)
            : $this->slideDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $bannerDataObject,
            $data,
            SlideInterface::class
        );

        return $this->slideRepository->save($bannerDataObject);
    }

    /**
     * Prepare data after save
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        if (!isset($data['banner_ids']) || !is_array($data['banner_ids'])) {
            $data['banner_ids'] = [];
        }
        if ($this->storeManager->isSingleStoreMode()) {
            $data['store_ids'] = 0;
        }
        if (empty($data['display_from'])) {
            $data['display_from'] = null;
        } else {
            $data['display_from'] = $this->convertDate($data['display_from']);
        }
        if (empty($data['display_to'])) {
            $data['display_to'] = null;
        } else {
            $data['display_to'] = $this->convertDate($data['display_to']);
        }
        if ($data['img_type'] == ImageType::TYPE_FILE) {
            $data['img_file_tmp'] = $data['img_file'];
            $data['img_file'] = $data['img_file'][0]['name'];
            if (isset($data['mobile_img_file'])) {
                $data['mobile_img_file_tmp'] = $data['mobile_img_file'];
                $data['mobile_img_file'] = $data['mobile_img_file'][0]['name'];
            } else {
                $data['mobile_img_file'] = null;
            }
            $data['img_url'] = '';
            $data['mobile_img_url'] = '';
        } else {
            $data['img_file'] = '';
            $data['mobile_img_file'] = '';
        }
        return $data;
    }

    /**
     * Prepare data after save
     *
     * @param array $data
     * @return $this
     * @throws LocalizedException
     */
    private function validate(array $data)
    {
        if (null != $data['display_from'] && null != $data['display_to']
            && strtotime($data['display_from']) >= strtotime($data['display_to'])
        ) {
            throw new LocalizedException(__('Display To cannot be equal or earlier than Display From.'));
        }
        return $this;
    }

    /**
     * Convert date
     *
     * @param string $dateFromForm
     * @return string
     */
    private function convertDate($dateFromForm)
    {
        return $this->dateTime->gmtDate(
            StdlibDateTime::DATETIME_PHP_FORMAT,
            strtotime($dateFromForm)
        );
    }
}
