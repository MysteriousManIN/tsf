<?php

    class CRUD{

        private $conn, $hostname, $dbname, $username, $password;

        public function __construct($h, $d, $u, $p){

            $this->hostname = $h;
            $this->dbname = $d;
            $this->username = $u;
            $this->password = $p;

        }

        public function open(): array{

            try{

                $this->conn = new PDO("mysql:hostname={$this->hostname};dbname={$this->dbname}", $this->username, $this->password);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                return [ "success" => true ];

            }catch(PDOException $e){

                return [
                    "success" => false,
                    "result" => [ "reason" => $e->getMessage() ]
                ];

            }

        }

        public function cud(string $sql): array{

            try{

                $this->conn->query($sql);

                return [ "success" => true ];

            }catch(PDOException $e){

                return [
                    "success" => false,
                    "result" => [ "reason" => $e->getMessage() ]
                ];

            }

        }

        public function r(string $sql): array{

            try{

                $res = $this->conn->query($sql);
                $res = $res->fetchAll(PDO::FETCH_ASSOC);
                $res = json_decode(json_encode($res, JSON_PRETTY_PRINT), true);

                return [
                    "success" => true,
                    "result" => [ "data" => $res ]
                ];

            }catch(PDOException $e){

                return [
                    "success" => false,
                    "result" => [ "reason" => $e->getMessage() ]
                ];

            }

        }

    }

?>