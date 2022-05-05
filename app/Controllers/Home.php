<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return view('welcome_message');
    }

    public function getPlayers(){
        // Recibimos los jugadores en un JSON
        try{
            $json = file_get_contents('php://input');
            $data = json_decode($json);
            
            // Creamos un array que contenga los equipos en el JSON
            $equipos = $this->getEquipos($data);
            foreach($data->jugadores as $d){
                if(in_array($d->equipo, $equipos)){
                    $temp[$d->equipo][] = array_merge(get_object_vars($d));
                }
            }

            // Obtenemos el bono de cada jugador y asignamos el sueldo completo correspondiente al jugador
            foreach($temp as $k => $v){
                $k = $this->getBono($v);
                $r['jugadores'][] = array_merge($k);
            }
            // Response que regresa el JSON a la peticion
            return $this->response->setStatusCode(200)->setJSON(json_encode($r));
        }
        catch(\Exception $e){
            echo "Ha ocurrido un error: ".$e->getMessage();
        }
    }

    private function getEquipos($data){
        try{
            $equipos = array();
            foreach($data->jugadores as $d){
                //verificamos si existe el equipo
                if(!in_array($d->equipo,$equipos)){
                    array_push($equipos, $d->equipo);
                }
            }
            return $equipos;
        }
        catch(\Exception $e){
            echo "ha ocurrido un error: ".$e->getMessage();
        }
    }

    private function getGPM($data){
        // CALCULAMOS LOS GOLES POR MES NECESARIOS SEGUN EL NIVEL
        try{
            switch($data['nivel']){
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

    private function getBono($equipo){
        /*
            El bono se divide en dos partes:
            1.- Goles individuales
                Para calcular el porcentaje de los goles individuales nos basamos en la tabla de goles por mes según el nivel
            2.- Goles por equipo
                Se saca la sumatoria de los goles por equipo entre la suma de los goles por mes según el nivel
        */
        $golesAnotados = 0;
        $golesNecesarios = 0;
        $golesEquipo = 0;

        // Calculando goles por equipo
        foreach($equipo as $jugador){
            $golesNecesarios = $this->getGPM($jugador);
            $golesAnotados = $golesAnotados + $jugador['goles'];
            $golesEquipo = $golesEquipo + $golesNecesarios;
        }
        // Sacamos el promedio por equipo
        $promedioEquipo = $golesAnotados / $golesEquipo * 100;
        
        // Calculando goles por individual
        $x = 0;
        foreach($equipo as $jugador){
            $golesNecesarios = $this->getGPM($jugador);
            $golesAnotados = $golesAnotados + $jugador['goles'];

            // Obtenemos el promedio individual
            $promedioIndividual = $jugador['goles'] / $golesNecesarios * 100;
            $porcentajeBono = ($promedioEquipo + $promedioIndividual) / 2;
            $bonoVariable = $porcentajeBono * $jugador['bono'] / 100;
            
            // Generamos el pago total del jugador
            $totalPago = $bonoVariable + $jugador['sueldo'];
            
            // Actualizamos el sueldo completo del array
            $arr = array('sueldo_completo' => $totalPago);
            $jugador = array_replace($jugador,$arr);

            // Actualizamos el jugador en el array del equipo
            $equipo[$x] = array_replace($equipo[$x], $arr);
            $x = $x + 1;
        }
        // Regresamos el equipo con los sueldos de los jugadores actualizados
        return $equipo;
    }
}
