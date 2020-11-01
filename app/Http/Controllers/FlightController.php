<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\Flight;
use App\Models\Group;

class FlightController extends Controller
{
    private $inbounds;
    private $outbounds;
    private $data;
    private $baseUrl = 'http://prova.123milhas.net/api/flights';
    private $fares = [];
    public $groups = [];
    private $cheapestPrice = PHP_INT_MAX;
    private $idCheapestGroup = null;
    private $uniqueId = 1;

    public function index() {
        $this->_getAllData(); //chamada http pra API
        $this->groups = $this->_groupData(); //Agrupamos com algoritmo nativo
        echo json_encode($this->response()); //resposta em json para o frontend
    }

    public function _getAllData() {
        $response = Http::get($this->baseUrl);
        $jsonData = $response->json();
        $this->data = $jsonData;
    }

    public function _getDataInbound() { //chamada http para API com apenas inbounds (somente exemplo)
        $response = Http::get($this->baseUrl.'?inbound=1');
        $jsonData = $response->json();
        $this->inbounds = $jsonData;
        
        return $this->inbounds;
    }

    public function _getDataOutbound() { //chamada http para API com apenas outbounds (somente exemplo)
        $response = Http::get($this->baseUrl.'?outbound=1');
        $jsonData = $response->json();
        $this->outbounds = $jsonData;
        
        return $this->outbounds;
    }

    public function response() {
        $response = [
            "flights" => $this->data,
            "groups" => $this->groups,
            "totalGroups" => count($this->groups),
            "totalFlights" => count($this->data),
            "cheapestPrice" => $this->cheapestPrice,
            "cheapestGroup" => $this->idCheapestGroup
        ];
        return $response;
    }

    /***
     *  Estrategia:
     *  Agrupar o array por "fare" e "tipo(oubound vs inbound)"
     */
    public function _groupData() {
        $flights = $this->data;
        $grupos = array();
        $fares = array();

        foreach ($flights as $flight) {
            $fare = $flight["fare"];
            $tipo = ($flight["outbound"] == 1) ? "outbound" : "inbound";
            $fares[$fare][$tipo][$flight["price"]][$flight["id"]] = $flight;
        }
                        
        foreach ($fares as $fare) {
            $arrayOutbound = array();
            $arrayInbound = array();
        
            // separando outbound por preco
            $outbound = $fare["outbound"];
            $prices = array_keys($outbound); //obtem todos os preços Outbound "disponíveis"
            
            foreach($prices as $price) { 
                $arrayOutbound[$price] = $outbound[$price];
            }
            
            $inbound = $fare["inbound"];
            $prices = array_keys($inbound); //obtem todos os preços Inbound "disponíveis"
            
            // separando inbound por preco
            foreach($prices as $price) {
                $arrayInbound[$price] = $inbound[$price];
            }
        
            foreach ($arrayOutbound as $precoOut => $out) { // mix em inbound e outbound para merge 
                foreach ($arrayInbound as $precoIn => $in) {
                    $precoGrupo = $precoOut + $precoIn;
                    $grupos[] = array(
                        "uniqueId" => $this->uniqueId,
                        "totalPrice" => $precoGrupo,
                        "outbound" => $out,
                        "inbound" => $in
                    );
        
                    if ($precoGrupo < $this->cheapestPrice) {
                        $this->cheapestPrice = $precoGrupo;
                        $this->idCheapestGroup = $this->uniqueId;
                    }
        
                    $this->uniqueId++;
                }
            }
        }
        return $grupos; 
    }
}
 