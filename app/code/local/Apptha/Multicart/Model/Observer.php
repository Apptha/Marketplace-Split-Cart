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
 * 
 * @package     Apptha_Multicart
 * @version     1.0
 * @author      Apptha Team <developers@contus.in>
 * @copyright   Copyright (c) 2015 Apptha. (http://www.apptha.com)
 * @license     http://www.apptha.com/LICENSE.txt
 *
 */
class Apptha_Multicart_Model_Observer {
    /**
     * Function To add Product From checkout
     *
     * @param
     *            observer
     * @return void
     */
    public function addProductToCart($observer) {
    	
    	
    	if (Mage::getStoreConfig ( "multicart/catalog/enabled" )) {
        /**
         * Getting Controller Name and Block Name
         */
         $controllerName = Mage::app ()->getRequest ()->getControllerName ();
     
        /**
         * Getting Action Name
         */
        $actionName = Mage::app ()->getRequest ()->getActionName ();
        /**
         * Getting Request Name
         */
         $requestName = $controllerName . "_" . $actionName;
        /**
         * Checking Controller Name
         */
        if ($controllerName == "cart" || $requestName=="checkout_cart_index") {
            /**
             * Checking whether checkout reached or not
             */
            $checkoutReached = Mage::getSingleton ( 'core/session' )->getCheckoutReached ();
            if ($checkoutReached == 1) {
            	
            	
                $items = Mage::getSingleton ( 'checkout/session' )->getQuote ()->getAllItems ();
                /**
                 * Incrementing Foreach Loop
                 */
                foreach ( $items as $item ) {
                    /**
                     * Defining Product Array
                     */
                    $productIds [] = $item->getProductId ();
                }
                $sessionItems = Mage::getSingleton ( 'core/session' )->getAppthaMpSplitCart ();
                
             
                
                /**
                 * Incrementing Foreach Loop
                 */
                foreach ( $productIds as $productId ) {
                    /**
                     * Unsetting Product Id from array
                     */
                    unset ( $sessionItems [$productId] );
                }
                /**
                 * Adding Product To Quote
                 */
                foreach ( $sessionItems as $key => $sessionvariables ) {
                    /**
                     * Adding Product To cart
                     */
                    $sessionvariables = ( int ) $sessionvariables;
                    /**
                     * Get cart
                     */
                    $cart = Mage::getSingleton ( 'checkout/cart' );
                    /**
                     * Init cart
                     */
                    $cart->init ();
                    /**
                     * add Product
                     */
                    $_product = Mage::getModel ( 'catalog/product' )->load ( $key );
                    /**
                     * Check whether simple or not
                     */
                    
                    
                    if ($_product->getTypeId () =="simple") {
                        /**
                         * Checking Whether Qty is > than Stock
                         */
                        $cart->addProduct ( $key, array (
                                'qty' => $sessionvariables 
                        ) );
                    } 
/**
 * 
 * Check whether product is downloadble or not
 */
                    elseif ($_product->getTypeId () == 'downloadable') {
                         $productId = $key;
                         /**
                         * Loading Product Details
                         */
                        $product = Mage::getModel('catalog/product')
                     
                        ->setStoreId(Mage::app()->getStore()->getId())->load($productId);
                         /**
                         * Incrementing foreach loop
                         */
                        $links = Mage::getModel ( 'downloadable/product_type' )->getLinks ( $product );
                        /**
                         * Incrementing foreach loop
                         */
                        foreach ( $links as $link ) {
                            /**
                             * Get link Id
                             */
                            $linkId = $link->getLinkId ();
                            $input = array (
                                    'qty' => 1,
                                    'links' => array(
                                            $linkId ) );
                            /**
                             * creating new varien object
                             */
                            $request = new Varien_Object ();
                            $request->setData ( $input );
                            
                            /**
                             * start adding the product
                             */ 
                          $cart->addProduct ( $key, $request );
                        }
                    } 
                    /**
                     * For other product types
                     */
                    else {
                        /**
                         * getting configurable data
                         */
                        $productDetails = Mage::getModel ( 'catalog/product' )->load ( $key );
                        /**
                         * Get Child Products
                         */
                        $childProducts = Mage::getModel ( 'catalog/product_type_configurable' )->getUsedProducts ( null, $productDetails );
                        $attributesData = $productDetails->getTypeInstance ()->getConfigurableAttributesAsArray ();
                        Mage::app ()->getStore ()->getStoreId ();
                        /**
                         * Incrementing foreach loop
                         */
                        foreach ( $attributesData as $attribute ) {
                            /**
                             * Check attribute code has been set already
                             */
                            if (isset ( $attribute ['attribute_code'] )) {
                                ?>
                                  <?php   $attributeCodeId=$attribute['store_label']; ?>
                                    <?php
                            }
                        }
                        /**
                         * Get Attribute Id
                         */
                        $attributeId = $attribute ['attribute_id'];
                        /**
                         * Get Attribute Code Id
                         */
                        $attributeCodeId = strtolower ( $attributeCodeId );
                        /**
                         * Incrementing Foreach loop
                         */
                        foreach ( $childProducts as $childProduct ) {
                            
                            $valueIndex = $childProduct [$attributeCodeId];
                            
                            $cId = $childProduct->getId ();
                          
                            /**
                             * Checking whether array exists or not
                             */
                            if (array_key_exists ( $cId, $sessionItems )) {
                                // this is our product ID
                                $options = array (
                                        "product" => $key, 
                                        "super_attribute" => array (
                                                $attributeId => $valueIndex 
                                        ),
                                        "qty" => $sessionvariables 
                                );
                                $cart->addProduct ( $key, $options );
                            }
                        }
                    }
                    Mage::getModel ( 'checkout/session' )->setCartWasUpdated ( true );
                    /**
                     * Save Cart
                     */
                    $cart->save ();
                    /**
                     * Update Cart
                     */
                    $cart->getQuote ()->setTotalsCollectedFlag ( false );
                    /**
                     * unseT cache
                     */
                    $cart->getQuote ()->getShippingAddress ()->unsetData ( 'cached_items_all' );
                    $cart->getQuote ()->getShippingAddress ()->unsetData ( 'cached_items_nominal' );
                    $cart->getQuote ()->getShippingAddress ()->unsetData ( 'cached_items_nonnominal' );
                    $cart->getQuote ()->collectTotals ();
                }
                /**
                 * Redirecting To cart Page
                 */
                Mage::getSingleton ( 'core/session' )->setCheckoutReached ( 0 );
                $cartUrl = Mage::getUrl ( 'checkout/cart', array (
                        '_secure' => true 
                ) );
                /**
                 * Redirect Frontend Controller
                 */
                Mage::app ()->getFrontController ()->getResponse ()->setRedirect ( $cartUrl );
                /**
                 * send response
                 */
                Mage::app ()->getResponse ()->sendResponse ();
                $controller = $observer->getControllerAction ();
                $controller->getRequest ()->setDispatched ( true );
                /**
                 * Set Flag
                 */
                $controller->setFlag ( '', Mage_Core_Controller_Front_Action::FLAG_NO_DISPATCH, true );
            }
        }
    }
   }
    /**
     * Removing other seller products in checkout
     * 
     * @param
     *            observer
     *            
     * @return void
     */
    public function addSellerProductToCheckout($observer) {
        /**
         * Checking whether module enabled or not
         */
        if (Mage::getStoreConfig ( "multicart/catalog/enabled" )){
        	
        	$getCheckouReached= Mage::getSingleton ( 'core/session' )->getCheckoutReached();
        	
        
        	if($getCheckouReached==1){
				return;
        	}
        	
        	if($getCheckoutReached!=1){
        	
            $controllerName = Mage::app ()->getRequest ()->getControllerName();
        	/**
        	 * Getting Action Name
        	*/
        	$actionName = Mage::app ()->getRequest ()->getActionName ();
        	/**
        	 * Getting Request Name
        	*/
        	 $requestName = $controllerName . "_" . $actionName;
        	/**
             * Setting checkout reached as 1
             */
          	$sellerIds = array ();
            /**
             * Getting Seller Id
             */
          	
          	
         
           $currentSellerId [] = Mage::app ()->getRequest ()->getParam ( 's' );
            /**
             * Getting Quote Items
             */
            $items = Mage::getSingleton ( 'checkout/session' )->getQuote ()->getAllItems ();
            /**
             * Defining Product Array
             */
            $product = array ();
            /**
             * Incrementing Foreach Loop
             */
            foreach ( $items as $item ) {
                /**
                 * Storing Product Details in Array
                 */
                $productId = $item->getProductId ();
                /**
                 * Getting Name
                 */
                $product [$productId] ['name'] = $item->getName ();
                /**
                 * Getting Sku
                 */
                $product [$productId] ['sku'] = $item->getSku ();
                /**
                 * Get Qty
                 */
                $product [$productId] ['qty'] = $item->getQty ();
                /**
                 * Get Price
                 */
                $product [$productId] ['price'] = $item->getPrice ();
                /**
                 * Get Product Id
                 */
                $product [$productId] ['id'] = $item->getProductId ();
            }
            Mage::getModel ( 'core/session' )->setMpSplitCart ( $product );
            /**
             * Get Count OF Items
             */
            $totalItems = count ( $items );
            /**
             * Incrementing Foreach Loop
             */
            foreach ( $items as $item ) {
                /**
                 * Defining Product Array
                 */
                $product [] = $productId = $item->getProductId ();
                /**
                 * Getting Qty
                 */
                $qty = $item->getQty ();
                /**
                 * Getting Price
                 */
                $price = $item->getPrice ();
                /**
                 * calculating Total
                 */
                $totalPrice = $qty * $price;
                /**
                 * Grand Total
                 */
                $grandTotal = $grandTotal + $totalPrice;
                /**
                 * Sub total
                 */
                $subTotal = $subTotal + $totalPrice;
                /**
                 * Define Array
                 */
                /**
                 * Checking Visibility Status
                 */
                $_product = Mage::getModel ( 'catalog/product' )->load($productId);
                /**
                 * Get Assign PRODUCT
                 */
                $assignProduct= $_product->getIsAssignProduct();
                /**
                 * Get Visibility
                 */
                $isVisibleProduct = $_product->isVisibleInSiteVisibility();
                /**
                 * Status
                 */
                $visibilty = $_product->getVisibility();
                /**
                 * Check associate prduct or not
                 */
                if($visibilty!=4 && $assignProduct==0  && $_product->getTypeId ()=="simple" ){
               	 array_push($productDatas,$productId);
                }
                $productDetails [$productId] = $qty;
                $sellerIdArray [] = $sellerId = Mage::getModel ( 'catalog/product' )->load ( $item->getProductId () )->getSellerId ();
                /**
                 * Check if sellerId is in array or not
                 */
                if (array_key_exists($sellerId,$sellerIds)) {
                    $currentProductArr = $sellerIds [$sellerId];
                    $currentProductArr [] = $productId;
                    $sellerIds [$sellerId] = $currentProductArr;
                } else {
                    $sellerIds [$sellerId] = array (
                            $productId 
                    );
                }
            }
            
            
          
            /**
             * Setting custom session
             */
            Mage::getSingleton ( 'core/session' )->setAppthaMpSplitCart ( $productDetails );
            $sellerDiffArray = array_diff( $sellerIdArray, $currentSellerId );
            /**
             * Make array as unique
             */
            $uniqueSellerIds = array_unique ( $sellerDiffArray );
            Mage::getModel ( 'core/session' )->setSellerIds($uniqueSellerIds);
            if (! empty ( $uniqueSellerIds )) {
            	/**
                 * Incrementing foreach loop
                 */
                foreach ( $uniqueSellerIds as $result ) {
                    $productIds = $sellerIds [$result];
                    /**
                     * Incrementing Foreach Loop
                     */
                    foreach ( $productIds as $productId ) {
                        /**
                         * Get Product Id
                         */
                        $productDatas [] = $productId;
                    }
                    /**
                     * Set Session Data
                     */
                    Mage::getSingleton ( 'core/session' )->setProductDatas ( $productDatas );
                }
                /**
                 * removable datas
                 */
                $removableCount = count ( $productDatas );
                $cartCount = $totalItems - $removableCount;
                /**
                 * Get Quote Id
                 */
                $quoteId = Mage::getSingleton ( 'checkout/session' )->getQuoteId ();
                /**
                 * Incrementing Foreach Loop
                 */
                foreach ( $productDatas as $productRemoveId ) {
                    /**
                     * Getting cart
                     */
                    $cartHelper = Mage::helper ( 'checkout/cart' );
                    /**
                     * Getting cart Items
                     */
                    $items = $cartHelper->getCart ()->getItems ();
                    /**
                     * Quote Collection
                     */
                    $removableDatasCollection = Mage::getModel ( 'sales/quote_item' )->getCollection ()->addFieldToFilter ( 'quote_id', $quoteId )->addFieldToFilter ( 'product_id', $productRemoveId );
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
                        
                   	$cartHelper->getCart ()->removeItem ( $itemId )->save ();
                     }
                    /**
                     * Unset cache and get totals from quote
                     */
                    $cartHelper->getQuote ()->setTotalsCollectedFlag ( false );
                    $cartHelper->getQuote ()->getShippingAddress ()->unsetData ( 'cached_items_all' );
                    $cartHelper->getQuote ()->getShippingAddress ()->unsetData ( 'cached_items_nominal' );
                    $cartHelper->getQuote ()->getShippingAddress ()->unsetData ( 'cached_items_nonnominal' );
                    $cartHelper->getQuote ()->collectTotals ();
                }
            }
            /**
             * Set Quote Items
             */
            $totalItems = Mage::getModel ( 'checkout/cart' )->setQuote ()->setItemsCount ( $cartCount );
            Mage::getSingleton ( 'core/session' )->setCheckoutReached ( 1 );
        }
       }
    }
    /**
     * Function to maintain cart items
     * 
     * @param
     *            observer
     */
    public function customerLogout($observer) {
        /**
         * Setting customer session to zero
         * 
         * @param
         *            observer
         */
        Mage::getSingleton ( 'core/session' )->setMpSplitCart ( '' );
    }
    
    /**
     * Function To Empty Cart
     * 
     * @param  observer
     */
    public function emptyCart($observer) {

    	if (Mage::getStoreConfig ( "multicart/catalog/enabled" )) {
    	/**
         * Getting Update Action
         */
        $updateAction = Mage::app ()->getRequest ()->getParam ( 'update_cart_action' );
        /**
         * Get Quote Id
         */
        $quoteId = Mage::getSingleton ( 'checkout/session' )->getQuoteId ();
        /**
         * Incrementing Foreach Loop
         */
        $productIds = explode ( ',', $updateAction );
        /**
         * Incrementing For each Loop
         */
        foreach ( $productIds as $productId ) {
            /**
             * Getting cart Items
             */
            $cartHelper = Mage::helper ( 'checkout/cart' );
            /**
             * removable datas
             */
            $removableDatasCollection = Mage::getModel ( 'sales/quote_item' )->getCollection ()->addFieldToFilter ( 'quote_id', $quoteId )->addFieldToFilter ( 'product_id', $productId );
            $removableDatas = $removableDatasCollection->getData ();
            /**
             * Incrementing For each Loop
             */
            foreach ( $removableDatas as $removableData ) {
                /**
                 * Removing In quote Id
                 */
                $itemId = $removableData ['item_id'];
                /**
                 * Remove Item
                 */
                $cartHelper->getCart ()->removeItem ( $itemId )->save ();
                Mage::getSingleton ( 'checkout/session' )->getQuote ()->setTotalsCollectedFlag ( false )->collectTotals ();
            }
        }
    }
   }
    /**
     * Function To check quote Quantity
     * 
     * @param
     *            observer
     */
    public function checkQuoteItemQty($observer) {
    	
    	if (Mage::getStoreConfig ( "multicart/catalog/enabled" )) {
        /**
         * Get Quote Items
         */
        $quoteItem = $observer->getEvent ()->getItem ();
        
        /* @var $quoteItem Mage_Sales_Model_Quote_Item */
        if (! $quoteItem || ! $quoteItem->getProductId () || ! $quoteItem->getQuote () || $quoteItem->getQuote ()->getIsSuperMode ()) {
            return $this;
        }
        
        /**
         * Get Qty
         */
        $qty = $quoteItem->getQty ();
        /**
         * Check if product in stock.
         * For composite products check base (parent) item stosk status
         */
        $stockItem = $quoteItem->getProduct ()->getStockItem ();
        $parentStockItem = false;
        if ($quoteItem->getParentItem ()) {
            $parentStockItem = $quoteItem->getParentItem ()->getProduct ()->getStockItem ();
        }
        /**
         * Check whether stock Available or not
         */
        if ($stockItem) {
            if (! $stockItem->getIsInStock () || ($parentStockItem && ! $parentStockItem->getIsInStock ())) {
                /**
                 * Add Error Info
                 */
                $quoteItem->addErrorInfo ( 'cataloginventory', Mage_CatalogInventory_Helper_Data::ERROR_QTY, Mage::helper ( 'cataloginventory' )->__ ( 'This product is currently out of stock.' ) );
                $quoteItem->getQuote ()->addErrorInfo ( 'stock', 'cataloginventory', Mage_CatalogInventory_Helper_Data::ERROR_QTY, Mage::helper ( 'cataloginventory' )->__ ( 'Some of the products are currently out of stock.' ) );
                return $this;
            } else {
                // Delete error from item and its quote, if it was set due to item out of stock
                $this->_removeErrorsFromQuoteAndItem ( $quoteItem, Mage_CatalogInventory_Helper_Data::ERROR_QTY );
            }
        }
        /**
         * Check item for options
         */
        $options = $quoteItem->getQtyOptions ();
        /**
         * Check whether Quantity >0
         */
        if ($options && $qty > 0) {
            $qty = $quoteItem->getProduct ()->getTypeInstance ( true )->prepareQuoteItemQty ( $qty, $quoteItem->getProduct () );
            $quoteItem->setData ( 'qty', $qty );
            /**
             * Check Whether Stoack Available or not
             */
            if ($stockItem) {
                /**
                 * Check Quantity Increments
                 */
                $result = $stockItem->checkQtyIncrements ( $qty );
                if ($result->getHasError ()) {
                    $quoteItem->addErrorInfo ( 'cataloginventory', Mage_CatalogInventory_Helper_Data::ERROR_QTY_INCREMENTS, $result->getMessage () );
                    
                    $quoteItem->getQuote ()->addErrorInfo ( $result->getQuoteMessageIndex (), 'cataloginventory', Mage_CatalogInventory_Helper_Data::ERROR_QTY_INCREMENTS, $result->getQuoteMessage () );
                } else {
                    // Delete error from item and its quote, if it was set due to qty problems
                    $this->_removeErrorsFromQuoteAndItem ( $quoteItem, Mage_CatalogInventory_Helper_Data::ERROR_QTY_INCREMENTS );
                }
            }
            $quoteItemHasErrors = false;
            /**
             * Incrementing Foreach loop
             */
            foreach ( $options as $option ) {
                /**
                 * Get Option Value
                 */
                $optionValue = $option->getValue ();
                /* @var $option Mage_Sales_Model_Quote_Item_Option */
                $optionQty = $qty * $optionValue;
                $increaseOptionQty = ($quoteItem->getQtyToAdd () ? $quoteItem->getQtyToAdd () : $qty) * $optionValue;
                /**
                 * Get Stock Item
                 */
                $stockItem = $option->getProduct ()->getStockItem ();
                /**
                 * Check Whether Product Configurable Or NOT
                 */
                if ($quoteItem->getProductType () == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                    /**
                     * Set Product Name
                     */
                    $stockItem->setProductName ( $quoteItem->getName () );
                }
                
                /* @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
                if (! $stockItem instanceof Mage_CatalogInventory_Model_Stock_Item) {
                    Mage::throwException ( Mage::helper ( 'cataloginventory' )->__ ( 'The stock item for Product in option is not valid.' ) );
                }
                /**
                 * define that stock item is child for composite product
                 */
                $stockItem->setIsChildItem ( true );
                /**
                 * don't check qty increments value for option product
                 */
                $stockItem->setSuppressCheckQtyIncrements ( true );
                /**
                 * Check Stock qty
                 */
                $qtyForCheck = $this->_getQuoteItemQtyForCheck ( $option->getProduct ()->getId (), $quoteItem->getId (), $increaseOptionQty );
                /**
                 * Getting Result
                 */
                $result = $stockItem->checkQuoteItemQty ( $optionQty, $qtyForCheck, $optionValue );
                if (! is_null ( $result->getItemIsQtyDecimal () )) {
                    $option->setIsQtyDecimal ( $result->getItemIsQtyDecimal () );
                }
                /**
                 * Checking whether result as Quantity update
                 */
                if ($result->getHasQtyOptionUpdate ()) {
                    $option->setHasQtyOptionUpdate ( true );
                    $quoteItem->updateQtyOption ( $option, $result->getOrigQty () );
                    $option->setValue ( $result->getOrigQty () );
                    /**
                     * if option's qty was updates we also need to update quote item qty
                     */
                    $quoteItem->setData ( 'qty', intval ( $qty ) );
                }
                if (! is_null ( $result->getMessage () )) {
                    $option->setMessage ( $result->getMessage () );
                    $quoteItem->setMessage ( $result->getMessage () );
                }
                if (! is_null ( $result->getItemBackorders () )) {
                    $option->setBackorders ( $result->getItemBackorders () );
                }
                /**
                 * Checking whether has error or not
                 */
                if ($result->getHasError ()) {
                    $option->setHasError ( true );
                    $quoteItemHasErrors = true;
                    /**
                     * Add Error Info
                     */
                   
                    
                    $quoteItem->getQuote ()->addErrorInfo ( $result->getQuoteMessageIndex (), 'cataloginventory', Mage_CatalogInventory_Helper_Data::ERROR_QTY, $result->getQuoteMessage () );
                } elseif (! $quoteItemHasErrors) {
                   /**
                    * remove error log
                    */
                   
                    $this->_removeErrorsFromQuoteAndItem ( $quoteItem, Mage_CatalogInventory_Helper_Data::ERROR_QTY );
                }
                
                $stockItem->unsIsChildItem ();
            }
        } 
        /**
         * Else condition
         */
        else {
            /* @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
            if (! $stockItem instanceof Mage_CatalogInventory_Model_Stock_Item) {
                Mage::throwException ( Mage::helper ( 'cataloginventory' )->__ ( 'The stock item for Product is not valid.' ) );
            }
            
            /**
             * When we work with subitem (as subproduct of bundle or configurable product)
             */
            if ($quoteItem->getParentItem ()) {
                $rowQty = $quoteItem->getParentItem ()->getQty () * $qty;
                /**
                 * we are using 0 because original qty was processed
                 */
                $qtyForCheck = $this->_getQuoteItemQtyForCheck ( $quoteItem->getProduct ()->getId (), $quoteItem->getId (), 0 );
            } else {
                $increaseQty = $quoteItem->getQtyToAdd () ? $quoteItem->getQtyToAdd () : $qty;
                $rowQty = $qty;
                $qtyForCheck = $this->_getQuoteItemQtyForCheck ( $quoteItem->getProduct ()->getId (), $quoteItem->getId (), $increaseQty );
            }
            /**
             * Get Product Custom Option
             */
            $productTypeCustomOption = $quoteItem->getProduct ()->getCustomOption ( 'product_type' );
            if (! is_null ( $productTypeCustomOption )) {
                // Check if product related to current item is a part of grouped product
                if ($productTypeCustomOption->getValue () == Mage_Catalog_Model_Product_Type_Grouped::TYPE_CODE) {
                    $stockItem->setProductName ( $quoteItem->getProduct ()->getName () );
                    $stockItem->setIsChildItem ( true );
                }
            }
            /**
             * Getting Result
             */
            $result = $stockItem->checkQuoteItemQty ( $rowQty, $qtyForCheck, $qty );
            
            if ($stockItem->hasIsChildItem ()) {
                $stockItem->unsIsChildItem ();
            }
            /**
             * Check If not Null
             */
            
            if (! is_null ( $result->getItemIsQtyDecimal () )) {
                $quoteItem->setIsQtyDecimal ( $result->getItemIsQtyDecimal () );
                if ($quoteItem->getParentItem ()) {
                    $quoteItem->getParentItem ()->setIsQtyDecimal ( $result->getItemIsQtyDecimal () );
                }
            }
            /**
             * Just base (parent) item qty can be changed
             * qty of child products are declared just during add process
             * exception for updating also managed by product type
             */
            if ($result->getHasQtyOptionUpdate () && (! $quoteItem->getParentItem () || $quoteItem->getParentItem ()->getProduct ()->getTypeInstance ( true )->getForceChildItemQtyChanges ( $quoteItem->getParentItem ()->getProduct () ))) {
                /**
                 * Set Data
                 */
                $quoteItem->setData ( 'qty', $result->getOrigQty () );
            }
            /**
             * Checking Null ornot
             */
            if (! is_null ( $result->getItemUseOldQty () )) {
                $quoteItem->setUseOldQty ( $result->getItemUseOldQty () );
            }
            /**
             * Checking Null ornot
             */
            if (! is_null ( $result->getMessage () )) {
                $quoteItem->setMessage ( $result->getMessage () );
            }
            
            if (! is_null ( $result->getItemBackorders () )) {
                $quoteItem->setBackorders ( $result->getItemBackorders () );
            }
        }
        return $this;
    }
    }
    /**
     * Function To remove quote Quantity
     * 
     * @param
     *            observer
     */
    protected function _removeErrorsFromQuoteAndItem($item, $code) {
         if (Mage::getStoreConfig ( "multicart/catalog/enabled" )) {
        /**
         * Check whether item has error or not
         */
        if ($item->getHasError ()) {
            $params = array (
                    'origin' => 'cataloginventory',
                    'code' => $code 
            );
/**
 * Remove error
 */
            $item->removeErrorInfosByParams ( $params );
        }
        /**
         * Get Quote
         */
        $quote = $item->getQuote ();
        /**
         * Get Quote Collection
         */
        $quoteItems = $quote->getItemsCollection ();
        $canRemoveErrorFromQuote = true;
        /**
         * Incrementing foreach loop
         */
        foreach ( $quoteItems as $quoteItem ) {
            /**
             * Checking Quote Item Id and
             */
            if ($quoteItem->getItemId () == $item->getItemId ()) {
                continue;
            }
            /**
             * Getting Error Info
             */
            $errorInfos = $quoteItem->getErrorInfos ();
            /**
             * Incrementing foreach loop
             */
            foreach ( $errorInfos as $errorInfo ) {
                if ($errorInfo ['code'] == $code) {
                    $canRemoveErrorFromQuote = false;
                    break;
                }
            }
            if (! $canRemoveErrorFromQuote) {
                break;
            }
        }
        /**
         * Quote Error
         */
        if ($quote->getHasError () && $canRemoveErrorFromQuote) {
            $params = array (
                    'origin' => 'cataloginventory',
                    'code' => $code 
            );
            $quote->removeErrorInfosByParams ( null, $params );
        }
        return $this;
    }
    }
    /**
     * Function To get quote Quantity
     * 
     * @param
     *            observer
     *            return qty
     */
    protected function _getQuoteItemQtyForCheck($productId, $quoteItemId, $itemQty) { 
       if (Mage::getStoreConfig ( "multicart/catalog/enabled" )) {
        /**
         * Get Item Qty
         */
        $qty = $itemQty;
        /**
         * Checking Whether isset Or not
         */
        if (isset ( $this->_checkedQuoteItems [$productId] ['qty'] ) && ! in_array ( $quoteItemId, $this->_checkedQuoteItems [$productId] ['items'] )) {
            $qty += $this->_checkedQuoteItems [$productId] ['qty'];
        }
        /**
         * Checkout quote Qty
         */
        $this->_checkedQuoteItems [$productId] ['qty'] = $qty;
        /**
         * Checkout quote Qty Id
         */
        $this->_checkedQuoteItems [$productId] ['items'] [] = $quoteItemId;
        /**
         * Return Qty
         */
        return $qty;
    }
    }
}