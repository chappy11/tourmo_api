<?php 
include_once(dirname(__FILE__)."/Data_format.php");

class Booking extends Data_format{

    public function __construct(){
        parent::__construct();
        $this->load->model(array("Booking_Model","User_Model","Motor_Model","Notification_Model"));
    }

    public function insert_post(){
        $data = $this->decode();
        $user_id = isset($data->user_id) ? $data->user_id : "";
        $motor_id = isset($data->motor_id) ? $data->motor_id : "";
        $date_start = isset($data->date_start) ? $data->date_start : "";
        $date_end = isset($data->date_end) ? $data->date_end : "";
        $time = isset($data->time) ? $data->time : "";
        $no_days = isset($data->no_days) ? $data->no_days : "";
        $arr = array(
            "user_id" => $user_id,
            "motor_id" => $motor_id,
            "time" => $time,
            "start_date" => $date_start,
            "end_date" => $date_end,
            "booking_status" => 0,
            "no_days" => $no_days
        ); 
        $check = $this->Booking_Model->checkpending($user_id,$motor_id);
        if(count($check) > 0){
            $this->res(0,null,"You Have Current Pending Transaction",0);
        }else{
               $resp = $this->Booking_Model->insert($arr);
                if($resp){
                    $this->res(1,null,"You Book Successfully",0);
                }else{
                    $this->res(0,null,"Something went wrong",0);
                }
        }
     
    }

    public function getbooking_get($id){
        $data = $this->Booking_Model->getbyid($id);
        if(count($data) > 0){
            $this->res(1,$data,"Data found",count($data));
        }else{
            $this->res(0,null,"Data not found",0);
        }
    }

    public function getbymotorid_get($id){
        $data = $this->Booking_Model->getbymotorid($id);
        if(count($data) > 0){
            $this->res(1,$data,"Data Found",0);
        }else{
            $this->res(0,null,"Data not found",0);
        }
    }

    public function getbookinglist_get($owner_id){
        $data = $this->Booking_Model->getmybookinglist($owner_id);
        if(count($data) > 0){
            $this->res(1,$data,"Data found",0);
        }else{
            $this->res(0,null,"Data not found",0);
        }
    }


    public function getdatelist_get($motor_id){
        $data = $this->Booking_Model->getdatelist($motor_id);
        if(count($data) > 0){
            $this->res(1,$data,"Data found",0);
        }else{
            $this->res(0,null,"Data not found",0);
        }
    }


    public function acceptbooking_post($booking_id){
        $arr = array(
            "booking_status" => 1
        );
        $bdata = $this->Booking_Model->getbyid($booking_id)[0];
        $mdata = $this->Motor_Model->getmotorbyid($bdata->motor_id)[0];
        $isAccept = $this->Booking_Model->update($booking_id,$arr);
      
        if($mdata->tourmopoints < ($bdata->total_amount * 0.15)){
            $this->res(1,null,"Your tourmopoints is insufficient",0);
        }else{
            if($isAccept){
                $r = array(
                    "onRent" => 1,
                    "tourmopoints" => $mdata->tourmopoints - ($bdata->total_amount * 0.15)
                );
                $onRent = $this->Motor_Model->update($bdata->motor_id,$r);
                if($onRent){
                    $x = array(
                        "isRent" => 1
                    );
                    $isRent = $this->User_Model->update($bdata->user_id,$x);
                    if($isRent){
                        $notif = array(
                            "notif_title" => "Booking Accepted",
                            "notif_body" => "Your Booking has successfully accepted",
                            "isRead" => 0,
                            "notif_type" => 2,
                            "user_id" => $bdata->user_id
                        );
                        $this->Notification_Model->insert($notif);
                        $this->res(1,null,"Successfully Accepted",0);
                    }else{
                        $this->res(0,null,"Something went wrong",0);
                    }
                }else{
                    $this->res(1,null,"Something went wrong",0);
                }
            }else{
                $this->res(1,null,"Something went wrong",0);
            }
    
        }

    }

    public function declinebooking_post($booking_id,$motor_id){
        $list = [];
        $bookaccepted = $this->Booking_Model->getbyid($booking_id)[0];
        $curr = $this->getDatesFromRange($bookaccepted->start_date,$bookaccepted->end_date);
       // print_r($curr);
        $other = $this->Booking_Model->getpending($motor_id);
        foreach($other as $val){
               $d = $this->getDatesFromRange($val->start_date,$val->end_date);
                $dat = array_intersect($d,$curr);
                if(count($dat) > 0){
                    array_push($list,$val->booking_id);
                }
            }
        if(count($list) > 0){
            foreach($list as $val){
                $id = $this->Booking_Model->getbyid($val->user_id);
                $decline = array(
                    "booking_status" => 3
                );
                $update = $this->Booking_Model->update($id,$decline);
                if($update){
                     $notif = array(
                            "notif_title" => "Booking Accepted",
                            "notif_body" => "Your Booking has successfully accepted",
                            "isRead" => 0,
                            "notif_type" => 2,
                            "user_id" => $id
                        );
                    $this->Notification_Model->insert($notif);
                }else{
                    $this->res(0,null,"Something went wrong while updating",0);
                }
               
                
            }
        }
    }

    public function getDatesFromRange($start, $end, $format='Y-m-d') {
    return array_map(function($timestamp) use($format) {
        return date($format, $timestamp);
    },
    range(strtotime($start) + ($start < $end ? 4000 : 8000), strtotime($end) + ($start < $end ? 8000 : 4000), 86400));
    }

   
}

?>