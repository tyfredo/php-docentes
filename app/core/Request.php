<?php
    class Request{
        public static function body() : array{
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true);

            return is_array($data) ? $data : [];
        }
    }

?>