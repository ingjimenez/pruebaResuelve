<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return view('welcome_message');
    }

    public function getPlayers(){
        try{
            // $json = file_get_contents('php://input');
            // $data = json_decode($json);
            // var_dump($data);

            $json = '{
                "jugadores" : [  
                   {  
                      "nombre":"Juan Perez",
                      "nivel":"C",
                      "goles":10,
                      "sueldo":50000,
                      "bono":25000,
                      "sueldo_completo":null,
                      "equipo":"rojo"
                   },
                   {  
                      "nombre":"EL Cuauh",
                      "nivel":"Cuauh",
                      "goles":30,
                      "sueldo":100000,
                      "bono":30000,
                      "sueldo_completo":null,
                      "equipo":"azul"
                   },
                   {  
                      "nombre":"Cosme Fulanito",
                      "nivel":"A",
                      "goles":7,
                      "sueldo":20000,
                      "bono":10000,
                      "sueldo_completo":null,
                      "equipo":"azul"
             
                   },
                   {  
                      "nombre":"El Rulo",
                      "nivel":"B",
                      "goles":9,
                      "sueldo":30000,
                      "bono":15000,
                      "sueldo_completo":null,
                      "equipo":"rojo"
             
                   }
                ]
            }';
            
            $data = json_decode($json);
            $ja = 0;
            $jr = 0;
            foreach($data->jugadores as $d){
                $gpm = $this->getGPM($d); //obtiene los goles por mes
                if($d->equipo == 'rojo'){
                    $jr = $jr+1;
                }
                else if($d->equipo == 'azul'){
                    $ja = $ja+1;
                }
                // var_dump($d);
            }
            echo "Equipo azul tiene $ja jugadores <br>";
            echo "Equipo rojo tiene $jr jugadores <br>";
            
        }
        catch(\Exception $e){
            echo "Ha ocurrido un error: ".$e->getMessage();
        }
    }

    public function getGPM($data){
        try{
            switch($data->nivel){
                case "A":
                    $gpm = 5;
                    break;
                case "B":
                    $gpm = 10;
                    break;
                case "C":
                    $gpm = 15;
                    break;
                case "Cuauh":
                    $gpm = 20;
                    break;
            }
            return $gpm;
        }
        catch(\Exception $e){
            return $e->getMessage();
        }
    }
}
