<?php 

    class Certification_Model extends CI_Model{

        private $table = "certification";

        public function __construct(){
            parent::__construct();
            $this->load->database();
        }

        public function insert($data){
            return $this->db->insert($this->table,$data);
        }
        
        public function getcert($service_id){
            $this->db->select("*");
            $this->db->where("service_id",$service_id);
            $this->db->from($this->table);
            $query = $this->db->get();
            return $query->result();
        }
    }
?>