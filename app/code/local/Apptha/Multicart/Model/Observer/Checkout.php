<?php
/**
 * Apptha
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.apptha.com/LICENSE.txt
 *
 * ==============================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * ==============================================================
 * This package designed for Magento COMMUNITY edition
 * Apptha does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * Apptha does not provide extension support in case of
 * incorrect edition usage.
 * ==============================================================
 *
 * @category    Apptha
 * @package     Apptha_Multicart
 * @version     1.0
 * @author      Apptha Team <developers@contus.in>
 * @copyright   Copyright (c) 2015 Apptha. (http://www.apptha.com)
 * @license     http://www.apptha.com/LICENSE.txt
 *
 */
class Apptha_Multicart_Model_Observer_Checkout extends Mage_Checkout_Model_Session{
    /**
     * After Placing the order,setting other seller products in cart quote
     *
     * @param
     *            observer
     * @return void
     */
    public function orderAfter($observer) {
        /**
         * Checking Whether Module Enabled or Not
         */
        if (Mage::getStoreConfig ( "multicart/catalog/enabled" )) {
            Mage::getSingleton ( 'core/session' )->setCheckoutReached(0);
            /**
             * Getting Order Id
            */
            $orderIds = $observer->getEvent ()->getOrderIds ();
            /**
             * Load By Order Id
            */
            $order = Mage::getModel ( 'sales/order' )->load ( $orderIds [0] );
            /**
             * Getting Customer Details
            */
            $customer = Mage::getSingleton ( 'customer/session' )->getCustomer ();
            /**
             * Get Order Items
            */
            $items = $order->getAllItems ();
            /**
             * Incrementing Foreach Loop
            */
            foreach ( $items as $item ) {
                /**
                 * Get Product Id
                 */
                $productIds [] = $item->getProductId ();
            }
            /**
             * Getting Session Items
             */
            $sessionItems = Mage::getSingleton ( 'core/session' )->getAppthaMpSplitCart ();
            /**
             * Incrementing Foreach Loop
            */
            foreach ( $productIds as $productData ) {
                /**
                 * Unset Product In Array
                 */
                unset ( $sessionItems [$productData] );
            }
            /**
             * Adding Product to cart
             *
             * @param
             *            s productId,Quantity
             */
            foreach ( $sessionItems as $key => $sessionvariables ) {
                /**
                 * Get Cart Items
                 */
                $cart = Mage::getSingleton ( 'checkout/cart' );
                $cart->init ();
                $_product = Mage::getModel ( 'catalog/product' )->load ( $key );
    
                if ($_product->getTypeId () == 'simple') {
                    /**
                     * Checking Whether Qty is > than Stock
                     */

                    $products = Mage::getModel('catalog/product')->load($key);
                    /**
                     * Checing Visibility Status
                    */
                    $assignProduct= $products->getIsAssignProduct();
                    $isVisibleProduct = $products->isVisibleInSiteVisibility();
                    
                    $visibilty = $products->getVisibility();
                    
                    if($visibilty== 4 && $assignProduct ==0 || $assignProduct ==1){
                    $cart->addProduct ( $key, array (
                            'qty' => $sessionvariables
                    ) );
                    }
                    
                    
                } elseif ($_product->getTypeId () == 'downloadable') {
                    $productId = $key;
                    // call the Magento catalog/product model
                    $product = Mage::getModel('catalog/product')
                    // set the current store ID
                    ->setStoreId(Mage::app()->getStore()->getId())
                    // load the product object
                    ->load($productId);
                    /**
                     * Get links
                    */
                    $links = Mage::getModel ( 'downloadable/product_type' )->getLinks ( $product );
    
                    foreach ( $links as $link ) {
                        $linkId = $link->getLinkId ();
                        /**
                         *  Here is the trick to add the right link id
                        */
    
                        $input = array (
                                'qty' => 1,
                                'links' => array (
                                        $linkId
                                )
                        );
                        $request = new Varien_Object ();
                        $request->setData ( $input );
    
                        // start adding the product
    
                        $cart->addProduct ( $key, $request );
                    }
                }
                /**
                 * Else Condition
                 */
                else {
                    /**
                     * Get Configurable details
                     */
                    $productData = Mage::getModel ( 'catalog/product' )->load ( $key );
                    $childProducts = Mage::getModel ( 'catalog/product_type_configurable' )->getUsedProducts ( null, $productData );
                    $attributes = $productData->getTypeInstance ()->getConfigurableAttributesAsArray ();
                    $storeId = Mage::app ()->getStore ()->getStoreId ();
                    /**
                     * Incrementing Foreach loop
                    */
                    foreach ( $attributes as $attribute ) {
                        /**
                         * Check attribute code has been set already
                         */
                        if (isset ( $attribute ['attribute_code'] )) {
                            ?>
                                                  <?php   $attributeStoreLabel=$attribute['store_label']; ?>
                                                    <?php
                            }
                        }
                        
                        $attributeId = $attribute ['attribute_id'];
                        
                        $attributeStoreLabel = strtolower ( $attributeStoreLabel );
                        /**
                         * Increment loop
                         */
                        foreach ( $childProducts as $childProduct ) {
                            $valueIndex = $childProduct [$attributeStoreLabel];
                            $cId = $childProduct->getId ();
                            if (array_key_exists ( $cId, $sessionItems )) {

                                 $cIdArray[$cId]=$valueIndex;
                            }
                                 }
                           foreach ( $cIdArray as $cId => $valueIndex ) {
                                       $options = array (
                                        "product" => $key, // this is our product ID
                                        "super_attribute" => array (
                                                $attributeId => $valueIndex 
                                        ),
                                        "qty" => $sessionvariables 
                                );
                                $cart->addProduct ( $key, $options );
                           }
                     
                    }
                    
                /**
                 * add Product with id ,quantity
                 */
                    $cart->getQuote ()->setTotalsCollectedFlag ( false )->collectTotals ();
                    $cart->save ();
                    
                }
                 /**
                 * saving in cart
                 */
                   /**
                 * Getting Quote Id
                 */
                $quoteId = Mage::getSingleton ( 'checkout/session' )->getQuoteId ();
               /**
                 * Load by Quote Id
                 */
                $quote = Mage::getModel ( 'sales/quote' )->load ( $quoteId );
                /**
                 * Check Whether Customer Logged In or not
                 */
 			    $cartHelper = Mage::helper ( 'checkout/cart' );
                $items = $cartHelper->getCart ()->getItems ();
                foreach($items as $item){
                  
                    $productId= $item->getProductId();
                    $products = Mage::getModel('catalog/product')->load($productId);
                    /**
                     * Checing Visibility Status
                    */
               		$assignProduct= $products->getIsAssignProduct();
                    $customerId= $products->getCustomerId();
                    $visibilty= $isVisibleProduct = $products->isVisibleInSiteVisibility();
                    if($visibilty!= 4 && $assignProduct ==0 && $products->getTypeId()=='simple'){
                
                        $removableDatasCollection = Mage::getModel ( 'sales/quote_item' )->getCollection ()->addFieldToFilter ( 'quote_id', $quoteId )->addFieldToFilter ( 'product_id', $productId );
                        $removableDatas = $removableDatasCollection->getData ();
                        /**
                         * Incrementing Foreach Loop
                        */
                        foreach ( $removableDatas as $removableData ) {
                            /**
                             * Getting Item Id
                             */
                            $itemId = $removableData ['item_id'];
                
                            /**
                             * Removed Id and save in cart
                             */
                            $cartHelper->getCart()->removeItem($itemId)->save();
                            Mage::getModel ( 'checkout/session' )->setCartWasUpdated ( true );
                            $cartHelper->getQuote ()->setTotalsCollectedFlag ( false );
                            $cartHelper->getQuote ()->getShippingAddress ()->unsetData ( 'cached_items_all' );
                            $cartHelper->getQuote ()->getShippingAddress ()->unsetData ( 'cached_items_nominal' );
                            $cartHelper->getQuote ()->getShippingAddress ()->unsetData ( 'cached_items_nonnominal' );
                            $cartHelper->getQuote ()->collectTotals ();
                
                            $totalItems = Mage::getModel ( 'checkout/cart' )->setQuote ()->setItemsCount ( $cartCount );
                        }
                         
                    }
                }
                
               if (Mage::getSingleton ( 'customer/session' )->isLoggedIn ()) {
                    /**
                     * Load the customer's data
                     */
                    $customer = Mage::getSingleton ( 'customer/session' )->getCustomer ();
                    /**
                     * Get Entity Id
                     */
                    $entityId = $customer->getEntityId ();
                    /**
                     * Get customer Email
                     */
                    $email = $customer->getEmail ();
                }
                /**
                 * saving in quote by customer Id
                 */
                $quote->setCustomerId ( $entityId )->setCustomerEmail ( $email );
                $quote->save ();
            }
        }
        /**
         * Load Customer Quote
         */
 public function loadCustomerQuote() { 
 $customerQuote = Mage::getModel('sales/quote') 
 ->setStoreId(Mage::app()->getStore()->getId())
  ->loadByCustomer(Mage::getSingleton('customer/session')->getCustomerId());
 if ($customerQuote->getId() && $this->getQuoteId() != $customerQuote->getId()) { // Removing old cart items of the customer.
 foreach ($customerQuote->getAllItems() as $item) { 
 $item->isDeleted(true); 
 if ($item->getHasChildren()) {
 foreach ($item->getChildren() as $child) { 
 $child->isDeleted(true);
 } } }
  $customerQuote->collectTotals()->save(); }
   else { 
 $this->getQuote()->getBillingAddress();
 $this->getQuote()->getShippingAddress(); 
 $this->getQuote()->setCustomer(Mage::getSingleton('customer/session')->getCustomer()) ->setTotalsCollectedFlag(false) ->collectTotals() ->save(); }
 return $this;
 } 
   
 /**
  * Customer Login Action
  */
 public function customerLogin($observer){
   $quoteId = Mage::getSingleton ( 'checkout/session' )->getQuoteId ();
   $quote = Mage::getModel ( 'sales/quote' )->load ( $quoteId ); 
   $cartHelper = Mage::helper ( 'checkout/cart' );
   $items = $cartHelper->getCart ()->getItems ();
 }
        
        
}