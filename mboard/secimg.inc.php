<?php

class PJ_SecurityImage {

        function PJ_SecurityImage($key) {
                $this->code = '';
                $this->key = $key;
        } // End PJ_SecurityImage

        function encrypt($plain_text) {
            $this->code = trim(crypt($plain_text,$this->key));
        } // End encrypt

        function checkCode($mystring,$checksum)
        {
            $this->encrypt($mystring);
            if ($this->code == $checksum)
                return true;
            else
                return false;
        } // End checkCode

        function printImage($random_number)
        {
            header("Content-type: image/jpeg");
            $im = @imagecreate(100, 20) or die("Cannot Initialize new GD image stream");
            $background_color = imagecolorallocate($im, 255, 255, 255);
            $text_color = imagecolorallocate($im, 255, 0, 0);

            for ($i=0;$i<strlen($random_number);$i++)
            {
                $display = substr($random_number,$i,1);
                $x = ($i*20) + rand(3,6);
                $y = rand(3,6);
                imagestring($im, 5, $x, $y, $display, $text_color);
            }

            for ($i=1;$i<100;$i++)
            {
                $cor_x = rand(1,100);
                $cor_y = rand(1,20);
                imagesetpixel($im,$cor_x,$cor_y,$text_color);
            }

            imagejpeg($im);
            imagedestroy($im);
        } // End printImage

        function get()
        {
            return $this->code;
        } // End get

} // End class PJ_SecurityImage

?>
