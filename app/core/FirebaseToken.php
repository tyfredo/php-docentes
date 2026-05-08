<?php
    class FirebaseToken{
        private array $config;

        public function __construct(){
            $this->config = require __DIR__ . '/../config/firebase.php';
        }
        private function base64UrlEncode(string $data) : string{
            return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        }

        public function getAccessToken(): string{
            $now=time();
            $header =[
                'alg' => 'RS256',
                'typ' => 'JWT'
            ];
            $payload = [
                'iss' => $this->config['client_email'],
                'scope' => $this->config['scope'],
                'aud' => $this->config['token_uri'],
                'iat' => $now,
                'exp' => $now + 3600
            ];
            $base64Header = $this->base64UrlEncode(json_encode($header));
            $base64Payload = $this->base64UrlEncode(json_encode($payload));

            $unsignedJwt = $base64Header . '.' . $base64Payload;

            openssl_sign(
                $unsignedJwt,
                $signature,
                $this->config['private_key'],
                OPENSSL_ALGO_SHA256
            );

            $jwt = $unsignedJwt . '.' . $this->base64UrlEncode($signature);

            $postData = http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            ]);

            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                    'content' => $postData,
                    'ignore_errors' => true
                ]
            ]);

            $response = file_get_contents($this->config['token_uri'], false, $context);
            $json = json_decode($response, true);
            if(!isset($json['access_token'])){
                throw new Exception('No se pudo obtener el token: ');
            }
            return $json['access_token'];


        }
    }

?>