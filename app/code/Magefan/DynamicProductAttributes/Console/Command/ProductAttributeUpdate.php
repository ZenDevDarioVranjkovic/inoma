<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\DynamicProductAttributes\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magefan\DynamicProductAttributes\Model\Config;
use Magefan\DynamicProductAttributes\Model\UpdateProductAttributes;

/**
 * Class ProductAttributeUpdate
 */
class ProductAttributeUpdate extends Command
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var UpdateProductAttributes
     */
    protected $updateProductAttributes;

    /**
     * @param Config $config
     * @param UpdateProductAttributes $updateProductAttributes
     * @param null $name
     */
    public function __construct(
        Config $config,
        UpdateProductAttributes $updateProductAttributes,
        $name = null
    ) {
        $this->config = $config;
        $this->updateProductAttributes = $updateProductAttributes;
        parent::__construct($name);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Zend_Db_Statement_Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->config->isEnabled()) {
            $this->updateProductAttributes->update();
            $output->writeln("Magefan Attributes have been applied.");
        } else {
            $output->writeln("Dynamic Product Attributes extension is disabled. Please turn on it.");
        }
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("magefan:dyc:product-attribute:update");
        $this->setDescription("Update Dynamic Product Attributes");
        parent::configure();
    }
}
