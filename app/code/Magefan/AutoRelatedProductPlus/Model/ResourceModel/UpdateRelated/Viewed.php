<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\AutoRelatedProductPlus\Model\ResourceModel\UpdateRelated;

use Magento\Framework\App\ResourceConnection;

class Viewed
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @return void
     * @throws \Zend_Db_Statement_Exception
     */
    public function execute()
    {
        $pastDate = date('Y-m-d H:i:s', strtotime('-30 days'));

        $this->resourceConnection->getConnection()->query(
            'SET session group_concat_max_len=15000'
        )->execute();

        $sql = 'INSERT INTO ' . $this->resourceConnection->getTableName('magefan_autorp_index_also_viewed') . ' (product_id, related_products_ids) (
            SELECT T2_product_id, @T2_related_products_ids := GROUP_CONCAT(T2_related_products_ids ORDER BY cnt DESC) FROM (
                SELECT m_product_id as T2_product_id, c_product_ids as T2_related_products_ids,cnt FROM (
                    SELECT m.product_id as m_product_id, c.product_id as c_product_ids, count(c.product_id) as cnt FROM report_viewed_product_index m
                        LEFT JOIN report_viewed_product_index c on m.customer_id IS NOT NULL and m.customer_id = c.customer_id and m.added_at >= "' . $pastDate . '" AND  c.added_at >= "' . $pastDate . '" AND
                            m.product_id != c.product_id
                        WHERE
                            m.added_at >= "' . $pastDate . '" AND  c.added_at >= "' . $pastDate . '"
                        GROUP BY m.product_id, c.product_id
                        ORDER BY cnt DESC
                    ) T
                ) T2
                WHERE T2_related_products_ids IS NOT NULL
                GROUP BY T2_product_id
            )
            ON DUPLICATE KEY UPDATE related_products_ids = @T2_related_products_ids';
        $this->resourceConnection->getConnection()->query($sql)->execute();
    }
}
