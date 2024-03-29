<?php 

    class ShopOrder_Model extends CI_Model{

        private $table_name = "shoporder";
        public function __construct(){
            
            parent::__construct();
            $this->load->database();
        }

        public function createShopOrder($data=array()){
            return $this->db->insert($this->table_name,$data);
        }

        public function getShopOrderByOrderId($order_id){
            $this->db->select("*");
            $this->db->from($this->table_name);
            $this->db->where("order_id",$order_id);
            $this->db->join("shop","shop.shop_id=shoporder.shop_id");
            $query = $this->db->get();
            return $query->result();
        }

        public function getShopOrderByShopOrderId($id){
            $this->db->select("*");
            $this->db->from($this->table_name);
            $this->db->where("shoporder.shoporder_id",$id);
            $this->db->join("shop","shop.shop_id=shoporder.shop_id");
            $query = $this->db->get();
            return $query->result();
        }

        public function getOrderShop($shop_id,$status){
            $this->db->select("*");
            $this->db->from($this->table_name);
            $this->db->where("shop_id",$shop_id);
            $this->db->where("shop_order_status",$status);
            $this->db->join("orders","orders.order_id=shoporder.order_id");
            $this->db->join("customer","customer.user_id=orders.user_id");
            $query = $this->db->get();
            return $query->result();
        }

        public function getorderByShopId($shop_id){
            $this->db->select("*");
            $this->db->from($this->table_name);
            $this->db->where("shoporder.shop_id",$shop_id);
            $this->db->join("shop","shop.shop_id=shoporder.shop_id");
            $query = $this->db->get();
            return $query->result();
        }

        public function update($id,$arr){
            return $this->db->update($this->table_name,$arr,"shoporder_id=".$id);
        }

        public function getorderbyrange($start,$end){
            $this->db->select("*");
            $this->db->from($this->table_name);
            $this->db->where("DATE(shopOrderUpdateAt)>=",$start);
            $this->db->where("DATE(shopOrderUpdateAt)<=",$end);
            $query =$this->db->get();

            return $query->result();
        }

        public function getOrderIdAndShopId($order_id,$shop_id){
            $this->db->select("*");
            $this->db->from($this->table_name);
            $this->db->where("order_id",$order_id);
            $this->db->where("shop_id",$shop_id);
            $query = $this->db->get();

            return $query->result();

        }

        public function getTransactionByStatus($status){
            $this->db->select("*");
            $this->db->from($this->table_name);
            $this->db->where("shop_order_status",$status);
            $this->db->join("shop","shop.shop_id=shoporder.shop_id");
            $this->db->join("orders","orders.order_id=shoporder.order_id");
            $this->db->join("customer","customer.user_id=orders.user_id");
            $query = $this->db->get();

            return $query->result();
        }
  
        public function getPendingOrder(){
            $this->db->select("*");
            $this->db->from($this->table_name);
            $this->db->where("shop_order_status",0);
            $query = $this->db->get();
            return $query->result();
        }
  
    }
?>