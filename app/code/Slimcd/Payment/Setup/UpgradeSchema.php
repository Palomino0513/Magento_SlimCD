<?php

namespace Slimcd\Payment\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();
        $quoteTable = 'quote';
        $orderTable = 'sales_order';
        $invoiceTable = 'sales_invoice';
        $creditmemoTable = 'sales_creditmemo';
        //Quote table
        $connection->addColumn(
            $setup->getTable($quoteTable),
            'slim_fee_type',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '255',
                'nullable' => true,
                'comment' => 'Extra fee type'
            ]
        );
        $connection->addColumn(
            $setup->getTable($quoteTable),
            'slim_fee',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '10,2',
                'default' => 0.00,
                'nullable' => true,
                'comment' => 'Extra fee'
            ]
        );
            
        //Order table
        $connection->addColumn(
            $setup->getTable($orderTable),
            'slim_fee_type',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '255',
                'nullable' => true,
                'comment' => 'Extra fee type'
            ]
        );
        $connection->addColumn(
            $setup->getTable($orderTable),
            'slim_fee',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '10,2',
                'default' => 0.00,
                'nullable' => true,
                'comment' => 'Extra fee'
            ]
        );
        //Invoice tables
        $connection->addColumn(
            $setup->getTable($invoiceTable),
            'slim_fee_type',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '255',
                'nullable' => true,
                'comment' => 'Extra fee type'
            ]
        );
        $connection->addColumn(
            $setup->getTable($invoiceTable),
            'slim_fee',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '10,2',
                'default' => 0.00,
                'nullable' => true,
                'comment' => 'Extra fee'
            ]
        );
        //creditmemoTable table
        $connection->addColumn(
            $setup->getTable($creditmemoTable),
            'slim_fee_type',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '255',
                'nullable' => true,
                'comment' => 'Extra fee type'
            ]
        );
        $connection->addColumn(
            $setup->getTable($creditmemoTable),
            'slim_fee',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '10,2',
                'default' => 0.00,
                'nullable' => true,
                'comment' => 'Extra fee'
            ]
        );
        $installer->endSetup();
    }
}
