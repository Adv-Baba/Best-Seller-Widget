<?php


namespace TechSolve\BestSellerWidget\Block\Widget;

use Magento\Widget\Block\BlockInterface;
use Magento\Framework\View\Element\Template;

class Bestsellerlist extends Template implements BlockInterface
{

    protected $_template = "widget/bestsellerlist.phtml";

    /**
     * Default value for products count that will be shown
     */
    const DEFAULT_PRODUCTS_COUNT = 4;
    const DEFAULT_IMAGE_WIDTH = 150;
    const DEFAULT_IMAGE_HEIGHT = 150;
    /**
     * Products count
     *
     * @var int
     */
    protected $_productsCount;
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;
    /**
     * [$_bestSellerCollectionFactory description]
     * @var [type]
     */
    protected $_bestSellerCollectionFactory;
    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * Image helper
     *
     * @var Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;
    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $_cartHelper;
    /**
     * [$_productFactory description]
     * @var [type]
     */
    protected $_productFactory;
    /**
     * [$request description]
     * @var [type]
     */
    protected $request;
    /**
     * [$registry description]
     * @var [type]
     */
    protected $registry;

        /**
     * [$_resourceFactory description]
     * @var [type]
     */
        protected $_resourceFactory;

    /**
     * [$_catalogProductTypeConfigurable description]
     * @var [type]
     */
    protected $_catalogProductTypeConfigurable;
    /**
     * [$collection description]
     * @var [type]
     */
    protected $collection;
    /**
     * [$_storeManager description]
     * @var [type]
     */
    protected $_storeManager;
    /**
     * [__construct description]
     * @param \Magento\Catalog\Block\Product\Context                                     $context                        [description]
     * @param \Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory    $_bestSellerCollectionFactory   [description]
     * @param \Magento\Reports\Model\Grouped\CollectionFactory                           $collectionFactory              [description]
     * @param \Magento\Reports\Helper\Data                                               $reportsData                    [description]
     * @param \Magento\Catalog\Model\ProductFactory                                      $_productFactory                [description]
     * @param \Magento\Framework\App\Request\Http                                        $request                        [description]
     * @param \Magento\Framework\Registry                                                $registry                       [description]
     * @param \Magento\Reports\Model\ResourceModel\Report\Collection\Factory             $resourceFactory                [description]
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable [description]
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection                    $collection                     [description]
     * @param \Magento\Store\Model\StoreManagerInterface                                 $storeManager                   [description]
     * @param array                                                                      $data                           [description]
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory $_bestSellerCollectionFactory,
        \Magento\Reports\Model\Grouped\CollectionFactory $collectionFactory,
        \Magento\Reports\Helper\Data $reportsData,
        \Magento\Catalog\Model\ProductFactory $_productFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry,
        \Magento\Reports\Model\ResourceModel\Report\Collection\Factory $resourceFactory,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
        ) {
        $this->_resourceFactory = $resourceFactory;
        $this->_bestSellerCollectionFactory = $_bestSellerCollectionFactory->create();
        $this->_collectionFactory = $collectionFactory;
        $this->_reportsData = $reportsData;
        $this->_productFactory = $_productFactory;
        $this->_imageHelper = $context->getImageHelper();
        $this->_cartHelper = $context->getCartHelper();
        $this->request = $request;
        $this->registry = $registry;
        $this->_catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
        $this->collection = $collection;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $data);
    }
    /**
     * Image helper Object
     */
    public function imageHelperObj(){
        return $this->_imageHelper;
    }
    /**
     * get featured product collection
     */
    public function getBestsellerProduct(){
        $limit = $this->getProductLimit();

        $resourceCollection = $this->_resourceFactory->create('Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection');
        $this->checkPageLocation($resourceCollection);
        $resourceCollection->setPeriod('month');
        foreach ($resourceCollection as $product) {
            $productIds[]=$this->getProductData($product->getProductId());
        }


        $collection = $this->collection->addIdFilter($productIds);
        $collection->addMinimalPrice()
        ->addFinalPrice()
        ->addTaxPercents()
        ->addAttributeToSelect('*')
        ->addStoreFilter($this->_storeManager->getStore()->getId());

        $collection->setPageSize($limit);
        return $collection;
    }

    /**
     * Get the configured limit of products
     * @return int
     */
    public function getProductLimit() {
        if($this->getData('productcount')==''){
            return self::DEFAULT_PRODUCTS_COUNT;
        }
        return $this->getData('productcount');
    }
    /**
     * Get the widht of product image
     * @return int
     */
    public function getProductimagewidth() {
        if($this->getData('imagewidth')==''){
            return self::DEFAULT_IMAGE_WIDTH;
        }
        return $this->getData('imagewidth');
    }
    /**
     * Get the height of product image
     * @return int
     */
    public function getProductimageheight() {
        if($this->getData('imageheight')==''){
            return self::DEFAULT_IMAGE_HEIGHT;
        }
        return $this->getData('imageheight');
    }
    /**
     * [checkPageLocation description]
     * @return [type] [description]
     */
    private function checkPageLocation($bestseller){
        if($this->request->getFullActionName() == 'catalog_category_view') {
            $category = $this->registry->registry('current_category');

            $bestseller->join(['secondTable' => 'catalog_category_product'], 'sales_bestsellers_aggregated_monthly.product_id = secondTable.product_id', ['category_id' => 'secondTable.category_id']);
            $bestseller->addFieldToFilter('category_id', ['eq' => $category->getId()]);
        }
    }

    public function isCategoryPage(){
        if($this->request->getFullActionName() == 'catalog_category_view') {
            return true;
        }
        return false;
    }
    /**
     * Get the add to cart url
     * @return string
     */
    public function getAddToCartUrl($product, $additional = [])
    {
        return $this->_cartHelper->getAddUrl($product, $additional);
    }
    /**
     * Return HTML block with price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $priceType
     * @param string $renderZone
     * @param array $arguments
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getProductPriceHtml(
        \Magento\Catalog\Model\Product $product,
        $priceType = null,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
        ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }
        $arguments['zone'] = isset($arguments['zone'])
        ? $arguments['zone']
        : $renderZone;
        $arguments['price_id'] = isset($arguments['price_id'])
        ? $arguments['price_id']
        : 'old-price-' . $product->getId() . '-' . $priceType;
        $arguments['include_container'] = isset($arguments['include_container'])
        ? $arguments['include_container']
        : true;
        $arguments['display_minimal_price'] = isset($arguments['display_minimal_price'])
        ? $arguments['display_minimal_price']
        : true;
        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');
        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                $arguments
                );
        }
        return $price;
    }
    /**
     * [getProductLoad description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getProductLoad($id){
        return $this->_productFactory->create()->load($id);
    }

    /**
     * [getProductData description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    private function getProductData($id){
        $parentByChild = $this->_catalogProductTypeConfigurable->getParentIdsByChild($id);
        if(isset($parentByChild[0])){
            $id = $parentByChild[0];
        }
        return $id;
    }

}


