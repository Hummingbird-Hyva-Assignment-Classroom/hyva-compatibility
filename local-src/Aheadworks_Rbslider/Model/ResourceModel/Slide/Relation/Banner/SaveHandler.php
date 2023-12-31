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
namespace Aheadworks\Rbslider\Model\ResourceModel\Slide\Relation\Banner;

use Magento\Framework\App\ResourceConnection;
use Aheadworks\Rbslider\Api\Data\SlideInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class SaveHandler
 *
 * @package Aheadworks\Rbslider\Model\ResourceModel\Slide\Relation\Banner
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(MetadataPool $metadataPool, ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $entityId = (int)$entity->getId();
        $bannerIds = $entity->getBannerIds();
        $bannerIdsOrig = $this->getBannerIds($entityId);
        $connection = $this->getConnection();
        $tableName = $this->resourceConnection->getTableName('aw_rbslider_slide_banner');

        $toInsert = array_diff($bannerIds, $bannerIdsOrig);
        if (count($toInsert)) {
            foreach ($toInsert as $bannerId) {
                $connection->insert(
                    $tableName,
                    ['slide_id' => $entityId, 'banner_id' => $bannerId, 'position' => 0]
                );
                $connection->insert(
                    $this->resourceConnection->getTableName('aw_rbslider_statistic'),
                    ['slide_banner_id' => $connection->lastInsertId(), 'view_count' => 0, 'click_count' => 0]
                );
            }
        }

        $toDelete = array_diff($bannerIdsOrig, $bannerIds);
        if (count($toDelete)) {
            $connection->delete(
                $tableName,
                ['slide_id = ?' => $entityId, 'banner_id IN (?)' => $toDelete]
            );
        }
        return $entity;
    }

    /**
     * Get banner IDs to which entity is assigned
     *
     * @param int $entityId
     * @return array
     */
    private function getBannerIds($entityId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName('aw_rbslider_slide_banner'), ['banner_id'])
            ->where('slide_id = :id');
        return $connection->fetchCol($select, ['id' => $entityId]);
    }

    /**
     * Get connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @throws \Exception
     */
    private function getConnection()
    {
        return $this->resourceConnection->getConnectionByName(
            $this->metadataPool->getMetadata(SlideInterface::class)->getEntityConnectionName()
        );
    }
}
