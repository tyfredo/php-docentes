<?php
    require_once __DIR__ . '/../core/Response.php';
    require_once __DIR__ . '/../core/FirestoreClient.php';

    class HealthController{
        public function index(): void{
            Response::json([
                'status' => 'ok',
                'message' => 'Backend Funcionando'
            ]);
        }

        public function firebase(): void{
            try{
                $client = new FirestoreClient();
                Response::json([
                'status' => 'ok',
                'message' => 'Conexion con la BD',
                ]);
                
            }catch(Exception $e){
                Response::json([
                     'status' => 'error',
                     'message' => $e->getMessage()
                ]);
            }
        }
    }
?>