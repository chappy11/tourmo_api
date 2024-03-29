<?php

    include_once(dirname(__FILE__)."/Data_format.php");

    class User extends Data_format{

        public function __construct(){
            parent::__construct();
            $this->load->model(array("User_Model","Notification_Model","UserLog_Model","Customer_Model","Shop_Model","Admin_Model",""));
        }
        //note:
        //user role:
        //0 admin
        //1 seller
        //2 customer
        
        
        public function warmup_post(){
            $data = $this->decode();
            $arr = array("email"=>$data->email);
            $this->res(1,$this->Customer_Model->checkIsEmailExist($data->email),"error",1);
        }

        public function isEmailExist($email,$type){
            $isExist = false;

            if($type === "shop" && $this->Shop_Model->checkShopEmailExist($email)){
                $isExist = true;
            }

            if($type === "customer" && $this->Customer_Model->checkIsEmailExist($email)){
                $isExist =  true;
            }

            return $isExist;

        }

        public function isMobileExist($mobile,$type){
            $isExist = false;

            if($type === "shop" && $this->Shop_Model->checkMobileExist($mobile)){
                $isExist = true;
            }

            if($type === "customer" && $this->Customer_Model->checkMobileNumberExist($mobile)){
                $isExist = true;
            }

            return $isExist;
        }

        //create account for customer
        public function signupcustomer_post(){
            $profile_picture =  $_FILES["profilePicture"]["name"];
            $username = $this->post("username");
            $password = $this->post("password");
            $firstname = $this->post("firstname");
            $middleInitial = $this->post("mi");
            $lastname = $this->post("lastname");
            $gender = $this->post("gender");
            $birthdate = $this->post("birthdate");
            $address = $this->post("address");
            $email = $this->post("email");
            $contact = $this->post("contact");

            $isEmailExist = $this->isEmailExist($email,"customer");
            $isMobileExist = $this->isMobileExist($contact,"customer");
            if($isEmailExist){
                
                $this->res(0,null,"Your Email is Already Exist",0);
            
            }else if($isMobileExist){
            
                $this->res(0,null,"Your Mobile Number is Alreay Exist",0);
            
            }else{
            
                $userData = array(
                    "username" => $username,
                    "password" => md5($password),
                    "user_roles" => 2, //customer
                    "user_status" => 0, //status is active
                );
    
                $isCreated = $this->User_Model->createUser($userData);
                
                if(!$isCreated){
                  $this->res(0,null,"Something went wrong",0);
                }else{
                   
                    $newUser = $this->User_Model->getNewUser(); //get latest user
              
                    try{
                     
                        $newCustomer = array(
                            "user_id" => $newUser[0]->user_id,
                            "email" => $email,
                            "contact" => $contact,
                            "firstname" => $firstname,
                            "lastname" => $lastname,
                            "middlename" => $middleInitial,
                            "gender" => $gender,
                            "birthdate" => $birthdate,
                            "addresss" => $address,
                            "profilePic" => "profiles/".$profile_picture
                        );
            
                        $createCustomer = $this->Customer_Model->createCustomer($newCustomer);
                    
                        if($createCustomer){
                            
                            $notif_data = array(
                                "notif_title"=>"New Registered Customer",
                                "notif_message"=> $newUser[0]->username." was successfully register to our system please check it for approval",
                                "notif_receiver"=> 1,
                                "notif_link"=>"/pendinguser",
                                "isRead"=>0
                            );

                            $isSuccessNotif = $this->Notification_Model->create($notif_data);
                            if($isSuccessNotif){
                                $this->res(1,null,"You Have Successfully Registered",0);
                                move_uploaded_file($_FILES['profilePicture']['tmp_name'],"profiles/".$profile_picture);
                            }else{
                                $this->res(0,null,"Something went wrong",0);
                            }
                            
                        }else{
                            $this->res(0,null,"Something went wrong",0);
                        }
                        
                      
                    } 
                    catch(Exception $e){
                        $this->User_Model->deleteUser($newUser[0]->user_id);
                        $this->res(0,null,"Something went wrong",0);
                    }
                }
    
            }          
        }

        //create account for shop
        public function createshop_post(){
            $shop_logo = $_FILES['shopLogo']['name'];
            $username = $this->post("username");
            $password = $this->post("password");
            $shop_email = $this->post("shopEmail");
            $shop_name = $this->post("shopName");
            $shop_description = $this->post("shopDescription");
            $firstname = $this->post("firstname");
            $middlename = $this->post("middlename");
            $lastname = $this->post("lastname");
            $address = $this->post("address");
            $shopContact = $this->post("contact");
            $cFname = $this->post("cfname");
            $cLname = $this->post("clname");
            $cNumber = $this->post("cNumber"); 
            $isEmailExist = $this->isEmailExist($shop_email,"shop");
            $isMobileNumberExist = $this->isMobileExist($shopContact,'shop');

            if($isEmailExist){
                
                $this->res(0,null,"This email is already exist",0);
            }else{

                $arr = array("username"=>$username);
                $this->res(1,$this->User_Model->checkDataExist($arr),"error",1);
            
    
                $userData = array(
                    "username" => $username,
                    "password" => md5($password),
                    "user_roles" => 1,
                    "user_status" => 0, 
                );
    
                $isCreated = $this->User_Model->createUser($userData);
    
                if(!$isCreated){
                    $this->res(0,null,"Something went wrong",0);
                }else{
                    $newUserData = $this->User_Model->getNewUser();
    
                    try{
                        $newShop = array(
                            "user_id" => $newUserData[0]->user_id,
                            "logo" => "shops/".$shop_logo,
                            "shopName" => $shop_name,
                            "shopEmail" => $shop_email,
                            "shopDescription" => $shop_description,
                            "ownerFirstname" => $firstname,
                            "ownerMiddlename" => $middlename,
                            "ownerLastname" => $lastname,
                            "shopAddress" => $address,
                            "subscription_id" => 0,
                            "shopContact" => $shopContact,
                            "contactPersonNumber"=>$cNumber,
                            "contactPersonFname"=>$cFname,
                            "contactPersonLname"=>$cLname
                        );
        
                        $isShopCreated = $this->Shop_Model->createShop($newShop);
        
                        if(!$isShopCreated){
                            $this->res(0,null,"Something went wrong, Sorry for Inconvinience",0);
                        }

                        $notif_data = array(
                            "notif_title"=>"New Registered Seller",
                            "notif_message"=> $newUserData[0]->username." was successfully register to our system please check it for approval",
                            "notif_receiver"=> 1,
                            "notif_link"=>"/pendingshops",
                            "isRead"=>0
                        );

                        $this->Notification_Model->create($notif_data);

                        $this->res(1,null,$shop_name." is successfully created!",0);
                        move_uploaded_file($_FILES['shopLogo']['tmp_name'],"shops/".$shop_logo);
               
                    }catch(Exception $e){
                        $this->User_Model->deleteUser($newUserData[0]->user_id);
                        $this->res(0,null,"Something went wrong",0);
                    }
                }

            }



      
        }
        
        //login
        public function login_post(){
            $data = $this->decode();
            $username = isset($data->username) ? $data->username : "";
            $password = isset($data->password) ? $data->password : "";
            $browserName = isset($data->browserName) ? $data->browserName : "";
       
            $user = $this->User_Model->login($username,md5($password));
                            
                if(count($user) < 1){
                    $this->res(0,null,"Invalid account please check your username or password",0);
                }else{
                    $pay = array(
                        "user_id"=>$user[0]->user_id,
                        "browserName" => $browserName,
                    );

                    if($user[0]->user_status === "1"){
                        $this->UserLog_Model->insert($pay);
                    }

                   
                    if($user[0]->user_status === "0"){
                        $this->res(0,null,"Your Account is Inactive",0);
                    }
                    else if($user[0]->user_roles == "1"){
                        $shopData = $this->Shop_Model->getShopByUserId($user[0]->user_id);
                    
                        $this->res(1,$shopData[0],"Successfully Login",0);
                    }
                    else if($user[0]->user_roles == "2"){
                        $customerData = $this->Customer_Model->getCustomerByUserId($user[0]->user_id);
    
                        $this->res(1,$customerData[0],"Successfully Login",0);
                    }else if($user[0]->user_roles == 0 || $user[0]->user_roles == 3){
                        $adminData = $this->Admin_Model->getAdminByUserId($user[0]->user_id);
                        
                        $this->res(1,$adminData[0],"Successfully Login",0);
                    }
               }              
        }

        public function getpendingcustomer_get(){
            $data = $this->Customer_Model->getPendingCustomer();
            if(count($data) > 0){
                $this->res(1,$data,"Data found",count($data));
            }else{
                $this->res(0,null,"Data not found",0);
            }
        }

        public function getusers_get($roles,$status){
            $data = $this->User_Model->getUserByStatus($roles,$status);
            $this->res(1,$data,"data found",0);
        }

        public function updatestatus_post(){
            $data = $this->decode();
            $user_id = $data->user_id;
            $status = $data->status;

            $userInfo = $this->User_Model->getuser($user_id)[0];

            $email = "";

            if($userInfo->user_roles == "1"){
                $email = $this->Shop_Model->getShopByUserId($user_id)[0]->shopEmail;
            }else if($userInfo->user_roles == "2"){
                $email = $this->Customer_Model->getCustomerByUserId($user_id)[0]->email;
            }

            $header = "";
            $message ="";

            if($status == "1"){
                $header = "Admin activated your account";
                $message = "Congrats your account has been activated you can do buy and sell now";
            }else{
                $header = "Admin deactivated your account";
                $message = "Your account has been deactivated by admin due for some reason";
            }

            $payload = array("user_status"=>$status);
           
            $isUpdateUser = $this->User_Model->updateUser($user_id,$payload);

                if($isUpdateUser){
                    $notif_data = array(
                        "notif_title"=>$header,
                        "notif_message"=> $message,
                        "notif_receiver"=> $user_id,
                        "notif_link"=>"/",
                        "isRead"=>0
                    );
                    if($status == "1"){
                        $this->approvedEmail($email);
                    }
                    $this->Notification_Model->create($notif_data);
                    $this->res(1,null,"Succesfully Update",0);
                }else{
                    $this->res(0,null,"Something went wrong",0);
                }
         
        }

        public function getuser_get($id,$type){
            if($type == "shop"){
                $shop = $this->Shop_Model->getShopByUserId($id);
                $this->res(1,$shop[0],"data found",0);
            }else{
                $customer = $this->Customer_Model->getCustomerByUserId($id);
                $this->res(1,$customer[0],"Data found",0);
            }            

        }

        public function getlogs_get(){
            $data = $this->UserLog_Model->getlog();
  
            $this->res(1,$data,"Data not found",0);
        }
  
        public function changepass_post(){
            $data = $this->decode();
            $password = $data->password;
            $id = $data->id;

            $payload = array("password"=>$password);
        
            $isUpdate = $this->User_Model->updateUser($id,$payload);

            if($isUpdate){
                $this->res(1,null,"Successfully Updating Your Password",0);
            }else{
                $this->res(0,null,"Something went wrong",0);
            }
        
        }

        public function update_post($id){
            $data= $this->decode();
            $user_id = $id;
            $user_data = $this->User_Model->getuser($user_id)[0];
            
            
            if($user_data->user_roles == "2"){
                $isCustomerUpdate = $this->Customer_Model->updateCustomerByUserId($user_id,$data);

                if($isCustomerUpdate){
                    $customer = $this->Customer_Model->getCustomerByUserId($user_id);
                    
                    $this->res(1,$customer[0],"Successfully Updated",0);
                }else{
                    $this->res(0,null,"Something went wrong",0);
                }
            }else if($user_data->user_roles=="1"){
                $isShopUpdated = $this->Shop_Model->updateShopByUserId($user_id,$data);
                
                if($isShopUpdated){
                    $shop = $this->Shop_Model->getShopByUserId($user_id);
                    
                    $this->res(1,$shop[0],"Successfully Updated",0);
                }else{
                    $this->res(0,null,"SOmething went wrong",0);
                }
            }
        }

        public function approvedEmail($email){
            $ht ="<html>
            <div style='margin: auto; width: 600px'>
              <h3 style='text-align: center'>Welcome to Petsociety</h3>
          
              <p style='text-align: center'>
            Congratulations! Your account has been successfully activated. You may now
                log in to Pet Society using your registered username and password. Get
                ready to explore the platform, buy products, and pets. Have fun!
              </p>
            </div>
          </html>";
            $config['protocol']    = 'smtp';
            $config['smtp_host']    = 'smtp.mailtrap.io';
            $config['smtp_port']    = '2525';
            $config['smtp_user'] = '7ec9d17b2163b3';
            $config['smtp_pass'] = '55c83a05d8d4cb';
            $config['charset']    = 'utf-8';
            $config['newline']    = "\r\n";
            $config['mailtype'] = 'html'; // or html
            $config['validation'] = TRUE; // bool whether to validate email or not      
            $this->load->library('email');

            $this->email->initialize($config);
            $this->email->from("no-reply@petsoceity.com");
            $this->email->to($email);
            $this->email->subject("Email Verificatoin Code");
            $this->email->message($ht);
  
            $this->email->send();
        }
    }

?>