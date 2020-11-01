<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    private $uniqueId;
    private $outbounds;
    private $inbounds;
    public $totalPrice;

    protected $fillable = [
        'uniqueId',
        'flights',
        'totalPrice',
        'outbounds',
        'inbounds',
        'cheapestPrice',
        'cheapestGroup',
    ];

    function setInbounds($flights) {
        $this->inbounds = $flights;
    }

    function setUniqueId($id) {
        $this->uniqueId = $id;
    }

    function getUniqueId() {
        return $this->uniqueId;
    }

    function setOutbounds($flights) {
        $this->outbounds = $flights;
    }

    function setPriceGroup() {
        $amount = 0;

        foreach($this->inbounds as $inbound) {
            foreach($this->outbounds as $outbound) {
                $amount = $outbound["price"] + $inbound["price"];
            }
        }
    
        $this->totalPrice = $amount;
    }
}
