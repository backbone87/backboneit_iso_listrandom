<?php

class ModuleIsotopeProductListRandom extends ModuleIsotopeProductList {

	public function generate() {
		$this->blnCacheProducts = method_exists('ModuleIsotopeProductList', 'getCacheExpiration');
		return parent::generate();
	}

	protected function getCacheExpiration() {
		return min(parent::getCacheExpiration(), 600);
	}

	protected function findProducts($arrCacheIDs = null) {
		$strCategoryIDs = implode(',', $this->findCategories($this->iso_category_scope));
		$strCategories = <<<EOT
AND c.page_id IN ($strCategoryIDs)
EOT;

		if(!BE_USER_LOGGED_IN) {
			$intTime = time();
			$strPublish = <<<EOT
AND p1.published = '1'
AND (p1.start = '' OR p1.start < $intTime)
AND (p1.stop = '' OR p1.stop > $intTime)
EOT;
		}

		if(is_array($arrCacheIDs) && $arrCacheIDs) {
			$strCacheIDs = implode(',', $arrCacheIDs);
			$strCache = <<<EOT
AND p1.id IN ($strCacheIDs)
EOT;
		}

		$this->iso_list_where && $strListWhere = 'AND ' . $this->iso_list_where;

		list($arrFilters, $arrSorting, $strWhere, $arrValues) = $this->getFiltersAndSorting();

		$strSelect = IsotopeProduct::getSelectStatement();
		$strQuery = <<<EOT
$strSelect
WHERE	p1.language = ''
$strCategories
$strPublish
$strCache
$strListWhere
$strWhere
GROUP BY p1.id
ORDER BY RAND()
EOT;
		$objProductData = $this->Database->prepare($strQuery)->execute($arrValues);

		return IsotopeFrontend::getProducts($objProductData, 0, true, $arrFilters, array());
	}

	protected function generatePagination() {
		return 0;
	}

}
