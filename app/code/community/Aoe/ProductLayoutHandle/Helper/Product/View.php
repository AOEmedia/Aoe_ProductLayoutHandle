<?php
class Aoe_ProductLayoutHandle_Helper_Product_View extends Mage_Catalog_Helper_Product_View {

	/**
	 * @var boolean
	 */
	protected $useProductLayoutUpdateHandle = NULL;

	/**
	 * @return boolean
	 */
	protected function getUseProductLayoutUpdateHandle() {
		if ($this->useProductLayoutUpdateHandle === NULL) {
			$this->useProductLayoutUpdateHandle = Mage::getStoreConfigFlag('catalog/frontend/use_product_layout_handle');
		}
		return $this->useProductLayoutUpdateHandle;
	}

	/**
	 * Inits layout for viewing product page
	 *
	 * @param Mage_Catalog_Model_Product $product
	 * @param Mage_Core_Controller_Front_Action $controller
	 *
	 * @return Mage_Catalog_Helper_Product_View
	 */
	public function initProductLayout($product, $controller)
	{
		$design = Mage::getSingleton('catalog/design');
		$settings = $design->getDesignSettings($product);

		if ($settings->getCustomDesign()) {
			$design->applyCustomDesign($settings->getCustomDesign());
		}

		$update = $controller->getLayout()->getUpdate();
		$update->addHandle('default');
		$controller->addActionLayoutHandles();

		$update->addHandle('PRODUCT_TYPE_' . $product->getTypeId());

		//CHANGE BEGIN
		if ($this->getUseProductLayoutUpdateHandle()) {
			$update->addHandle('PRODUCT_' . $product->getId());
		}
		//CHANGE END

		$controller->loadLayoutUpdates();

		// Apply custom layout update once layout is loaded
		$layoutUpdates = $settings->getLayoutUpdates();
		if ($layoutUpdates) {
			if (is_array($layoutUpdates)) {
				foreach($layoutUpdates as $layoutUpdate) {
					$update->addUpdate($layoutUpdate);
				}
			}
		}

		$controller->generateLayoutXml()->generateLayoutBlocks();

		// Apply custom layout (page) template once the blocks are generated
		if ($settings->getPageLayout()) {
			$controller->getLayout()->helper('page/layout')->applyTemplate($settings->getPageLayout());
		}

		$currentCategory = Mage::registry('current_category');
		$root = $controller->getLayout()->getBlock('root');
		if ($root) {
			$controllerClass = $controller->getFullActionName();
			if ($controllerClass != 'catalog-product-view') {
				$root->addBodyClass('catalog-product-view');
			}
			$root->addBodyClass('product-' . $product->getUrlKey());
			if ($currentCategory instanceof Mage_Catalog_Model_Category) {
				$root->addBodyClass('categorypath-' . $currentCategory->getUrlPath())
					->addBodyClass('category-' . $currentCategory->getUrlKey());
			}
		}

		return $this;
	}
}