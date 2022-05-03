<?php

namespace App\Controllers;

use CodeIgniter\HTTP\Response;
use PhpCsFixer\Fixer\Alias\ArrayPushFixer;

class Home extends BaseController
{
    public function index()
    {
        return view('welcome_message');
    }

    public function getPlayers(){
        try{
            $json = file_get_contents('php://input');
            $data = json_decode($json);
            // var_dump($data);

            // $json = '{
            //     "jugadores" : [  
            //         {  
            //             "nombre":"Juan Perez",
            //             "nivel":"C",
            //             "goles":10,
            //             "sueldo":50000,
            //             "bono":25000,
            //             "sueldo_completo":null,
            //             "equipo":"rojo"
            //         },
            //         {  
            //             "nombre":"EL Cuauh",
            //             "nivel":"Cuauh",
            //             "goles":30,
            //             "sueldo":100000,
            //             "bono":30000,
            //             "sueldo_completo":null,
            //             "equipo":"azul"
            //         },
            //         {  
            //             "nombre":"Cosme Fulanito",
            //             "nivel":"A",
            //             "goles":7,
            //             "sueldo":20000,
            //             "bono":10000,
            //             "sueldo_completo":null,
            //             "equipo":"azul"             
            //         },
            //         {  
            //             "nombre":"El Rulo",
            //             "nivel":"B",
            //             "goles":9,
            //             "sueldo":30000,
            //             "bono":15000,
            //             "sueldo_completo":null,
            //             "equipo":"rojo"
            //         }
            //     ]
            // }';
            
            $data = json_decode($json);
            //$nj = count($data->jugadores); //conociendo el número de jugadores
            $equipoAzul = [];
            $equipoRojo = [];
            foreach($data->jugadores as $d){
                if($d->equipo == "azul"){
                    array_push($equipoAzul, get_object_vars($d));
                }
                else if($d->equipo == "rojo"){
                    array_push($equipoRojo, get_object_vars($d));
                }
            }
            // echo "<pre>";
            // print_r($equipoAzul);
            // echo "<br>";
            // print_r($equipoRojo);
            // echo "</pre>";
            $arrEquipoAzul = $this->getBono($equipoAzul);
            $arrEquipoRojo = $this->getBono($equipoRojo);
            // 89N3PDyZzakoH7W6n8ZrjGDDktjh8iWFG6eKRvi3kvpQ

            $arr = array_merge($arrEquipoAzul,$arrEquipoRojo);
            return $arr;

            // $gpm = $this->getGPM($d); //obtiene los goles por mes
            // $porcentajeBono = $this->getBono($d, $gpm);
            // echo $porcentajeBono;
            // echo $d->nombre." anotó ".$d->goles." goles de ".$gpm."<br>";
        }
        catch(\Exception $e){
            echo "Ha ocurrido un error: ".$e->getMessage();
        }
    }

    public function getGPM($data){
        // CALCULAMOS LOS GOLES POR MES Y SACAMOS EL PORCENTAJE
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
                Para calcular el porcentaje de los goles individuales nos basamos en la tabla de goles por mes segun el nivel
            2.- Goles por equipo
                Se saca la sumatoria de los goles por equipo entre la suma de los goles por mes segun el nivel
        */
        // $jugadores = count($equipo);
        $golesAnotados = 0;
        $golesNecesarios = 0;
        $golesEquipo = 0;

        //calculando goles por equipo
        foreach($equipo as $jugador){
            $golesNecesarios = $this->getGPM($jugador);
            $golesAnotados = $golesAnotados + $jugador['goles'];
            $golesEquipo = $golesEquipo + $golesNecesarios;
        }
        $promedioEquipo = $golesAnotados / $golesEquipo * 100;
        // echo "Goles Anotados: $golesAnotados, Goles por Equipo: $golesEquipo. Promedio: $promedioEquipo% <br>";

        //calculando goles por individual
        $x = 0;
        foreach($equipo as $jugador){
            $golesNecesarios = $this->getGPM($jugador);
            $golesAnotados = $golesAnotados + $jugador['goles'];
            ///////// Goles Individuales /////////
            $promedioIndividual = $jugador['goles'] / $golesNecesarios * 100;
            // echo $jugador['nombre']." -> Goles anotados: ".$jugador['goles'].", Goles Necesarios: $golesNecesarios. Promedio Individual: $promedioIndividual% <br>";
            $porcentajeBono = ($promedioEquipo + $promedioIndividual) / 2;
            $bonoVariable = $porcentajeBono * $jugador['bono'] / 100;
            $totalPago = $bonoVariable + $jugador['sueldo'];
            // echo $jugador['nombre']." <br> Sueldo Fijo: ".$jugador['sueldo']." <br>Bono: $bonoVariable <br> Total a pagar: $totalPago <br>";
            // echo "<br>";

            $arr = array('sueldo_completo' => $totalPago);
            $jugador = array_replace($jugador,$arr);
            // $this->prettyPrint($jugador);
            $equipo[$x] = array_replace($equipo[$x], $arr);
            $x = $x + 1;
        }
        //$this->prettyPrint($equipo);
        return $equipo;
    }

    public function prettyPrint($data){
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }
}
