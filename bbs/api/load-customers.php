<?php

    header("Content-Type:application/json");

    require_once "lib/main.php";

    $conn = new CRUD(HOSTNAME, DBNAME, USERNAME, PASSWORD);
    $conn->open();

    $conn->cud(<<<STR
        create table if not exists customer_records(
            id int auto_increment,
            name varchar(32) not null,
            email_id varchar(32) not null unique,
            balance float default(0) not null,
            primary key (id)
        )
    STR);

    $res = $conn->cud(<<<STR
        create table if not exists transaction_records(
            id int auto_increment,
            sid int not null,
            rid int not null,
            amount float not null,
            date_time datetime not null,
            primary key (id),
            foreign key (sid) references customer_records (id),
            foreign key (rid) references customer_records (id)
        )
    STR);


    $customers = [
        [
            "name" => "Chitrasena",
            "email_id" => "chitrasena@bbs.com",
            "balance" => 220
        ],
        [
            "name" => "Indradyumna",
            "email_id" => "indradyumna@bbs.com",
            "balance" => 4000
        ],
        [
            "name" => "Revati",
            "email_id" => "revati@bbs.com",
            "balance" => 1400
        ],
        [
            "name" => "Meghavarna",
            "email_id" => "meghavarna@bbs.com",
            "balance" => 100
        ],
        [
            "name" => "Vasudeva",
            "email_id" => "vasudeva@bbs.com",
            "balance" => 6000
        ],
        [
            "name" => "Yayati",
            "email_id" => "yayati@bbs.com",
            "balance" => 180
        ],
        [
            "name" => "Takshaka",
            "email_id" => "takshaka@bbs.com",
            "balance" => 500
        ],
        [
            "name" => "Kalki",
            "email_id" => "kalki@bbs.com",
            "balance" => 1200
        ],
        [
            "name" => "Urvashi",
            "email_id" => "urvashi@bbs.com",
            "balance" => 300
        ],
        [
            "name" => "Pradyumna",
            "email_id" => "pradyumna@bbs.com",
            "balance" => 1000
        ]
    ];

    foreach($customers as $cust){
        
        [ "name" => $name, "email_id" => $email_id, "balance" => $balance ] = $cust;

        $res = $conn->cud("INSERT INTO customer_records (name, email_id, balance) VALUES ('${name}', '${email_id}', ${balance})");
        print_r($res);

    }

?>