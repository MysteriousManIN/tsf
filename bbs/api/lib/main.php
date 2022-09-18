<?php

    require_once "CRUD.php";

    define("HOSTNAME", "localhost");
    define("USERNAME", "root");
    define("PASSWORD", "");
    define("DBNAME", "bbs");

    define("ID_PREFIX", "CUST");

    $conn = new CRUD(HOSTNAME, DBNAME, USERNAME, PASSWORD);
    $conn->open();

    function customer(string $id, array $options = []): array{

        global $conn;        
        $fields = "id, name";

        if(isset($options["email_id"]) && $options["email_id"]) $fields .= ", email_id";
        if(isset($options["balance"]) && $options["balance"]) $fields .= ", balance";

        if($id === "all") $sql = "select ${fields} from customer_records";
        else{ 

            $id = degenerateId($id);
            $sql = "select ${fields} from customer_records where binary id = ${id}";

        }

        $res = $conn->r($sql);

        if($res["success"]){

            $data = $res["result"]["data"];

            if(count($data) > 0){

                $data = array_map(function($ele){
                    $ele["id"] = generateId($ele["id"]);
                    return $ele;
                }, $data);

                return [
                    "success" => true,
                    "result" => [
                        "code" => 1,
                        "data" => $data
                    ]
                ];

            }else{

                return [
                    "success" => false,
                    "result" => [
                        "code" => 2,
                        "message" => "customer's record not found at id ${id}"
                    ]
                ];

            }

        }

    }

    function transferMoney(string $sid, string $rid, float $amount): array{

        global $conn;

        function updateBalance(int $id, float $balance){

            global $conn;
            $conn->cud("update customer_records set balance = ${balance} where binary id = ${id}");

        }

        $res = customer($sid, [ "balance" => 1 ]);

        if($res["success"]) $sbal = $res["result"]["data"][0]["balance"];
        else return $res;

        $res = customer($rid, [ "balance" => 1 ]);
        
        if($res["success"]) $rbal = $res["result"]["data"][0]["balance"];
        else return $res;

        if($sbal >= $amount){

            $sbal -= $amount;
            $rbal += $amount;

            $sid = degenerateId($sid);
            $rid = degenerateId($rid);

            updateBalance($sid, $sbal);
            updateBalance($rid, $rbal);

            date_default_timezone_set("Asia/Kolkata");
            $datetime = date("Y-m-d H:i:s");
            $res = $conn->cud("insert into transaction_records (sid, rid, amount, date_time) values (${sid}, ${rid}, ${amount}, '${datetime}')");

            if($res["success"]){

                return [
                    "success" => true,
                    "result" => [
                        "code" => 1,
                        "message" => "transactions has been done"
                    ]
                ];

            }
            

        }else{

            return [
                "success" => false,
                "result" => [
                    "code" => 3,
                    "message" => "balance is not sufficient"
                ]
            ];

        }

    }

    function transaction(string $id): array{

        global $conn;
        $id = degenerateId($id);

        $res = $conn->r("select sid, rid, amount, date_time from transaction_records where binary sid = ${id} OR rid = ${id}");

        if($res["success"]){

            $transaction_data = $res["result"]["data"];

            if(count($transaction_data) > 0){

                $cust_list = [];
                foreach(customer("all")["result"]["data"] as $cust){
                    [ "id" => $cid, "name" => $cname ] = $cust;
                    $cust_list[$cid] = $cname;
                }

                $res = [];
                foreach($transaction_data as $t){

                    [ "sid" => $sid, "rid" => $rid, "amount" => $amount, "date_time" => $datetime ] = $t;
                    
                    $d["date_time"] = $datetime;
                    $d["amount"] = $amount;

                    if($sid == $id){
                        $rid = generateId($rid);
                        $d["action"] = "sent";
                        $d["customer"] = [ "id" => $rid, "name" => $cust_list[$rid] ];
                    }else if($rid == $id){
                        $sid = generateId($sid);
                        $d["action"] = "recieve";
                        $d["customer"] = [ "id" => $sid, "name" => $cust_list[$sid] ];
                    }

                    array_push($res, $d);

                }
                
                rsort($res);
                
                return [
                    "success" => true,
                    "result" => [
                        "code" => 1,
                        "data" => $res
                    ]
                ];

            }else{

                return [
                    "success" => false,
                    "result" => [
                        "code" => 2,
                        "message" => "transaction is empty"
                    ]
                ];

            }

        }

    }

    function generateId($idnum): string{

        $size = 4;

        $idnum = (string) $idnum;
        $len = strlen($idnum);

        if($len !== $size){

            for($i = 0, $zeros = "" ; $i < $size - $len ; $zeros .= "0", $i++);
            $idnum = $zeros . $idnum;

        }

        $id = ID_PREFIX . $idnum;

        return $id;

    }

    function degenerateId(string $id): int{

        $idnum = explode(ID_PREFIX, $id)[1];
        $idnum = (int) $idnum;

        return $idnum;  

    }

?>