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
            
            // Creamos variables para los equipos (después se deberán sacar los equipos automáticamente)
            $equipoAzul = [];
            $equipoRojo = [];
            // Asignamos los jugadores a los equipos correspondientes
            foreach($data->jugadores as $d){
                if($d->equipo == "azul"){
                    array_push($equipoAzul, get_object_vars($d));
                }
                else if($d->equipo == "rojo"){
                    array_push($equipoRojo, get_object_vars($d));
                }
            }

            // Obtenemos los bonos de cada jugador
            $arrEquipoAzul = $this->getBono($equipoAzul);
            $arrEquipoRojo = $this->getBono($equipoRojo);

            // Creamos un array para agregar los jugadores con su sueldo completo
            $arr['jugadores'] = array_merge($arrEquipoAzul,$arrEquipoRojo);

            // Convertimos el array a JSON y lo regresamos a la petición realizada
            return json_encode($arr);
        }
        catch(\Exception $e){
            echo "Ha ocurrido un error: ".$e->getMessage();
        }
    }

    public function getGPM($data){
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

    public function getBono($equipo){
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
