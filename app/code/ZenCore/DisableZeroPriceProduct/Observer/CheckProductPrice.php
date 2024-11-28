<?php
namespace ZenCore\DisableZeroPriceProduct\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class CheckProductPrice implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $price = $product->getPrice();

        if ($price == 0) {
            // Throw an exception to stop adding to the cart
            throw new LocalizedException(__('Nije moguće dodati ovaj artikal u košaricu zbog greša pri kreiranju cjene, molimo Vas da pokušate kasnije.'));
        }
    }
}
