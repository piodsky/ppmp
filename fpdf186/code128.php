<?php


class PDF_Code128 extends FPDF {
    protected $T128; // tableau des codes 128
    protected $ABCset = "";
    protected $Aset = "";
    protected $Bset = "";
    protected $Cset = "";
    protected $SetFrom;
    protected $SetTo;
    protected $JStart;
    protected $JSwap;
    protected $crypt = "";

    function __construct($orientation='P', $unit='mm', $size='A4') {
        parent::__construct($orientation,$unit,$size);
        $this->Init();
    }

    function Init() {
        $this->T128 = array();
        $this->ABCset = "";
        $this->Aset = "";
        $this->Bset = "";
        $this->Cset = "";

        for ($i = 0; $i <= 95; $i++) {
            $this->ABCset .= chr($i);
        }

        $this->Aset = $this->ABCset;
        $this->Bset = $this->ABCset;
        $this->Cset = "0123456789";

        $this->SetFrom = array();
        $this->SetTo = array();

        for ($i = 32; $i <= 126; $i++) {
            $this->SetFrom[] = chr($i);
            $this->SetTo[] = chr($i);
        }

        $this->JStart = array(chr(104), chr(105), chr(106)); // StartA, StartB, StartC
        $this->JSwap = array(chr(101), chr(100), chr(99)); // SwapA, SwapB, SwapC
    }

    function Code128($x, $y, $code, $w, $h) {
        $this->Barcode128($code);
        $this->DrawBarcode($x, $y, $w, $h);
    }

    function Barcode128($code) {
        // This is a dummy implementation that encodes Code 128-B only
        $this->crypt = chr(104); // Start with Code B
        $checksum = 104;
        for ($i = 0; $i < strlen($code); $i++) {
            $char = ord($code[$i]) - 32;
            $this->crypt .= chr($char);
            $checksum += ($char) * ($i + 1);
        }
        $this->crypt .= chr($checksum % 103); // checksum
        $this->crypt .= chr(106); // Stop
    }

    function DrawBarcode($x, $y, $w, $h) {
        $barCharWidth = $w / (strlen($this->crypt) * 11);
        $this->SetFillColor(0);
        for ($i = 0; $i < strlen($this->crypt); $i++) {
            $bars = $this->EncodeChar(ord($this->crypt[$i]));
            for ($j = 0; $j < 11; $j++) {
                if ($bars[$j] == '1') {
                    $this->Rect($x + ($i * 11 + $j) * $barCharWidth, $y, $barCharWidth, $h, 'F');
                }
            }
        }
    }

    function EncodeChar($code) {
        // A minimal hardcoded bar pattern representation (not accurate for all 128)
        return str_pad(decbin($code), 11, "0", STR_PAD_LEFT);
    }
}
