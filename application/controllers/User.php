<?php 
include_once (dirname(__FILE__) ."/Data_format.php");

class User extends Data_format{

    public function __construct(){
        parent::__construct();
        $this->load->database();
        $this->load->model(array("User_Model"));
    }


    //view all user
    public function userlist_get(){
      $data = $this->User_Model->user_list();
        if(count($data)<1){
            $this->res(0,null,"no data founds",0);
        }
        else{
            $this->res(1,$data,"data found",0);
        }
    }

    //register all user
    public function register_post(){
        $data = $this->decode();
        
            $dat = array(
                "email" => $data->email,
                "password" => $data->password,
                "user_pic" => "profiles/download.png",
                "firstname" => $data->fname,
                "lastname" => $data->lname,
                "contact" => $data->contact,
                "birthday" => "",
                "street" => $data->sitio,
                "barangay" => $data->brgy,
                "user_type" => "user",
                "user_status" => "active",
                "sub_id" => 0

            );
            // $this->res(1,$dat,"Data",0);
            $res = $this->User_Model->register($dat);
            if($res){
                $this->res(1,null,"success",0);
            }else{
                $this->res(0,null,"error network",0);
            }
    }
        
    
    
    //login admin
    public function loginAdmin_post(){
        $data = $this->decode();
        $email = isset($data->email) ? $data->email : "";
        $password = isset($data->password) ? $data->password : "";
        if(empty($email) || empty($password)){
            $this->res(0,null,"Fillout All Fields",0);
        }else{
            $res = $this->User_Model->login_admin($email,$password);
            if(count($res) > 0){
                $this->res(1,$res,"Successfully Login",1);
            }else{
                $this->res(0,null,"Wrong Credential",0);
            }
        }
    }


    //login user
    public function login_post(){
        $d = $this->decode();
       
        $email = isset($d->email) ? $d->email : "";
        $password = isset($d->password) ? $d->password : "";
        
        if(empty($email) || empty($password)){
            $this->res(1,null,"Fill out all Fields",0);
        }else{
           $res = $this->User_Model->login($email,$password);
           if(count($res)>0){
                $this->res(1,$res,"Successfully login",0);
           }else{
                $this->res(0,null,"Wrong credential",0);
           }
            
        }
    }
  
    //update user details
    public function update_post(){
        $d = $this->decode();
        $user_id = isset($d->user_id) ? $d->user_id : "";
        $username = isset($d->username) ? $d->username : "";
        $password = isset($d->password) ? $d->password : "";
        
        $data = array(
            "username"=>$username,
            "password"=>$password
        );

        $res = $this->User_Model->update($user_id,$data);
        if($res){
            $this->res(1,null,"successfully updated");
        }else{
            $this->res(0,null,"error updated");
        }
    }
    public function sample_get(){
        echo "wew";
    }
}


?>