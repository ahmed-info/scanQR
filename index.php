<?php
    
?>
<html>
    <head>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/webrtc-adapter/3.3.3/adapter.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.1.10/vue.min.js"></script>
<script type="text/javascript" src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <video id="preview" width="100%"></video>
                </div>
                <div class="col-md-6">
                    <label>SCAN QR CODE</label>
                    <input type="text" name="text" id="text" readonyy="" placeholder="scan qrcode" class="form-control">
					<br>
					<br>
					<input type="text" name="text" id="textDec" readonyy="" placeholder="scan qrcode" class="form-control">
                </div>
            </div>
        </div>

		<?php 
		
		function lzw_compress($string) {
        // compression
        $dictionary = array_flip(range("\0", "\xFF"));
        $word = "";
        $codes = array();
        for ($i=0; $i <= strlen($string); $i++) {
            $x = substr($string, $i, 1);
            if (strlen($x) && isset($dictionary[$word . $x])) {
                $word .= $x;
            } elseif ($i) {
                $codes[] = $dictionary[$word];
                $dictionary[$word . $x] = count($dictionary);
                $word = $x;
            }
        }
        
        // convert codes to binary string
        $dictionary_count = 256;
        $bits = 8; // ceil(log($dictionary_count, 2))
        $return = "";
        $rest = 0;
        $rest_length = 0;
        foreach ($codes as $code) {
            $rest = ($rest << $bits) + $code;
            $rest_length += $bits;
            $dictionary_count++;
            if ($dictionary_count >> $bits) {
                $bits++;
            }
            while ($rest_length > 7) {
                $rest_length -= 8;
                $return .= chr($rest >> $rest_length);
                $rest &= (1 << $rest_length) - 1;
            }
        }
        return $return . ($rest_length ? chr($rest << (8 - $rest_length)) : "");
    }
    
    /** LZW decompression
    * @param string compressed binary data
    * @return string original data
    */
    function lzw_decompress($binary) {
        static $word;
        // convert binary string to codes
        $dictionary_count = 256;
        $bits = 8; // ceil(log($dictionary_count, 2))
        $codes = array();
        $rest = 0;
        $rest_length = 0;
        for ($i=0; $i < strlen($binary); $i++) {
            $rest = ($rest << 8) + ord($binary[$i]);
            $rest_length += 8;
            if ($rest_length >= $bits) {
                $rest_length -= $bits;
                $codes[] = $rest >> $rest_length;
                $rest &= (1 << $rest_length) - 1;
                $dictionary_count++;
                if ($dictionary_count >> $bits) {
                    $bits++;
                }
            }
        }
        
        // decompression
        $dictionary = range("\0", "\xFF");
        $return = "";
        foreach ($codes as $i => $code) {
            $element = $dictionary[$code];
            if (!isset($element)) {
                $element = $word . $word[0];
            }
            $return .= $element;
            if ($i) {
                $dictionary[] = $word . $element[0];
            }
            $word = $element;
        }
        return $return;
    }
		?>
        <script>
           let scanner = new Instascan.Scanner({ video: document.getElementById('preview')});
           Instascan.Camera.getCameras().then(function(cameras){
               if(cameras.length > 0 ){
                   scanner.start(cameras[0]);
               } else{
                   alert('No cameras found');
               }

           }).catch(function(e) {
               console.error(e);
           });

           scanner.addListener('scan',function(c){
               
               document.getElementById('text').value=c;
               sessionStorage.setItem("before", c);
               document.getElementById("textDec").value=sessionStorage.getItem("before");
               let temp = sessionStorage.getItem("before");
               console.log(temp);
           });

        </script>

    </body>
</html>