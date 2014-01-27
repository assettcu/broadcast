<?php

class AudioClass
{
    public $audiotype       = "speech";
    public $audiotypes      = array(
        "speech",
        "morse",
    );
    
    public $postspeed       = "M";
    public $message         = "";
    public $audio_path      = "";
    public $audio_webpath   = "";
    
    public function __construct($message=null)
    {
        $this->message = $message;
        $this->init();
    }
    
    public function init()
    {
        # Currently doesn't convert on instantiation
    }
    
    public function convert()
    {
        $convert_function = "convert_to_".$this->audiotype;
        $this->$convert_function();
    }
    
    public function convert_to_morse()
    {
        $post_fields = array(
            "postspeed"         => "F",
            "postrawtext"       => $this->message,
            "postrate"          => 18,
            "postfarnsworth"    => 15,
            "postfrequency"     => 1000,
            "submit"            => "Create an mp3 file from this text",
            "remLen"            => (200-strlen($this->message)),
        );
        $post_fields = http_build_query($post_fields);
        
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,"http://www.morseresource.com/morse/makemorse.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $server_output = curl_exec($ch);
        
        curl_close($ch);
        
        if(preg_match("/<a\shref=\"out\/[A-Za-z0-9\-]+\.mp3/",$server_output,$matches)) {
            $filename = str_replace("<a href=\"","",$matches[0]);
            $filepath = "http://www.morseresource.com/morse/".$filename;
            $mp3 = file_get_contents($filepath);
            $putfile = LOCAL_LIBRARY_PATH."audio/morse/" . str_replace("out/","",$filename);
            file_put_contents($putfile,$mp3);
            return true;
        }
        
        return false;
    }
    
    public function convert_to_speech()
    {

        $words = urlencode($this->message);
         
        // Name of the MP3 file generated using the MD5 hash
        $filename  = md5($words).".mp3";
          
        // Save the MP3 file in this folder with the .mp3 extension 
        $file = LOCAL_LIBRARY_PATH."audio/speech/" . $filename;
         
        // If the MP3 file exists, do not create a new request
        if (!file_exists($file)) {
            $mp3 = file_get_contents('http://translate.google.com/translate_tts?ie=UTF-8&q='.$words.'&tl=en&total=1&idx=0&prev=input');
            file_put_contents($file, $mp3);
        }
        
        $this->audio_path = $file;
        $this->audio_webpath = WEB_LIBRARY_PATH."audio/speech/".$filename;
        
        return true;
    }
    
    public function render_playback($has_autoplay=true)
    {
        $autoplay = ($has_autoplay) ? "autoplay=\"autoplay\"" : "";
        ob_start();
        echo '<audio controls="controls" '.$autoplay.'>';
        echo '<source src="'.$this->audio_webpath.'" type="audio/mp3" />';
        echo '</audio>';
        $contents = ob_get_contents();
        ob_end_clean();
        
        echo $contents;
    }
}
