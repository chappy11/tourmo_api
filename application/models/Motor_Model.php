<?php 
    class Motor_Model extends CI_Model{

        $table = "vehicle";
        public function __construct(){
            parent::__construct();
            $this->load->database();
        }

        public function addMotor($data=array()){
            return $this->db->insert($table,$data);
        }

        public function getmotorbyid($motor_id){
            $this->db->select("*");
            $this->db->from($table);
            $this->db->where("motor_id",$motor_id);
            $query = $this->db->get();
            return $query->result();
        }

        public function getmotorbyuser($user_id){
            $this->db->select("*");
            $this->db->from($table);
            $this->db->where("user_id",$user_id);
            $query = $this->db->get();
            return $query->result();
        }
        
        public function update($id,$data=array()){
            return $this->db->update($table,$data,"motor_id=".$id);
        }

    }

?>