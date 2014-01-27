<?php
/**
 * The service "interfax" can only send to one fax machine per developer account.
 * A paid account is needed to send to any number.
 * 
 * Until then, this class has the account information and fax number of the developer account.
 */
class FaxClass
{
    
    public function fax()
    {
        /**************** Settings begin **************/
     
        $username = 'assett'; // Enter your Interfax username here
        $password = 'southernize1'; // Enter your Interfax password here
        $faxnumber = '+13037351994'; // Enter your designated fax number here in the format +[country code][area code][fax number], for example: +12125554874
        $texttofax = 'Test fax'; // Enter your fax contents here
        $filetype = 'TXT'; // If $texttofax is regular text, enter TXT here. If $texttofax is HTML enter HTML here
         
        /**************** Settings end ****************/
         
        $client = new SoapClient("http://ws.interfax.net/dfs.asmx?wsdl");
        
        $params = new stdClass;
        $params->Username  = $username;
        $params->Password  = $password;
        $params->FaxNumber = $faxnumber;
        $params->Data      = $texttofax;
        $params->FileType  = $filetype;
         
        // $faxResult = $client->SendCharFax($params);
         
        var_dump($faxResult);
    }
}
