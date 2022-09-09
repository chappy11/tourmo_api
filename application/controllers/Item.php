<?php 
    include_once(dirname(__FILE__)."/Data_format.php");

    class Item extends Data_format{
        
        public function __construct(){
            parent::__construct();
            $this->load->model(array("Product_Model","Shop_Model","Subscription_Model"));
        }

        public function createproduct_post(){
            $product_pic = $_FILES['pic']['name'];
            $name = $this->post("name");
            $description = $this->post("description");
            $category_id =$this->post("category_id");
            $price = $this->post("price");
            $stock = $this->post("stock");
            $unit = $this->post("unit");
            $shop_id = $this->post("shop_id");
        

            $this->Shop_Model->checkIsSubscriptionExpire($shop_id);
            $isSubscribe = $this->Shop_Model->getShopByid($shop_id)[0];
            if($this->Shop_Model->hasSubscription($shop_id)){
                $productData = array(
                    "shop_id" => $shop_id,
                    "productImage" => "products/".$product_pic,
                    "productName" => $name,
                    "category_id" => $category_id,
                    "productDescription" => $description,
                    "price" => $price,
                    "stock" => $stock,
                    "isAvailable" => 1,
                    "product_del" => 0,
                    "unit" => $unit,
                );
        
                $isCreated = $this->Product_Model->createNewProduct($productData);
        
                if($isCreated){
                    move_uploaded_file($_FILES['pic']['tmp_name'],"products/".$product_pic);
                    $this->res(1,null,"Successfully Added to you shop",0);
                }else{
                    $this->res(0,null,"Something went wrong",0);
                }
            }else{
                $this->res(0,null,"You cannot Add Item as for now please update your subscription first",0);
            }
        }
        
        public function product_get($product_id){
            $product = $this->Product_Model->getProductById($product_id);
        
            if(count($product) > 0){
                $this->res(1,$product[0],"Data found",0);
            }else{
                $this->res(0,null,"Data not found",0);
            }
        }
        
        public function products_get(){
            $products = $this->Product_Model->displayProducts();
            if(count($products) > 0){
                $this->res(1,$products,"Data found",count($products));
            }else{
                $this->res(0,null,"Data not found",0);
            }
        }
        
        public function myproducts_get($shop_id){
            $myproducts = $this->Product_Model->getProductByShopId($shop_id);
        
            if(count($myproducts) > 0){
                $this->res(1,$myproducts,"Data found",0);
            }else{
                $this->res(0,null,"Data not found",0);
            }
        }
  
        public function displayproducts_get(){
            $products = $this->Product_Model->displayProduct();
            if(count($products) > 0){
                $this->res(1,$products,"Data found",0);
            }else{
                $this->res(0,null,"Data not found",0);
            }
        }
        
        public function updatestock_post(){
            $data = $this->decode();
            $item_id = $data->itemId;
            $no_stock_added = $data->noStockAdded;
            $type = $data->type;
            $itemData = $this->Product_Model->getProductById($item_id)[0];
            $updatedStock = 0;
            
            if($type === 'add'){

                $updateStock = $itemData->stock + $no_stock_added;
            
            }else if($type === 'out'){
            
                $updatedStock = $itemData->stock - $no_stock_added;

            
            }

            if($updatedStock < 0){
                $this->res(0,null,"You cannot update stock below zero",0);
            }else{
                $updateData = array(
                    "stock" => $updatedStock,
                );
    
                $update = $this->Product_Model->updateProduct($item_id,$updateData);
    
                if($update){
                    $this->res(1,null,"Successfully Update",0);
                }else{
                    $this->res(0,null,"Something went wrong",0);
                }
            }

          
        }
    }

?>