<?php

    /*

    Address lookup with PHP
    Simple demo to search for addresses via our API on form submit and returns the full address.

    Full place name lookup API documentation:-
    https://developers.alliescomputing.com/postcoder-web-api/address-lookup
    
    */

    if (array_key_exists("input", $_GET)) {
        
        $page = (array_key_exists("page", $_GET) ? $_GET['page'] : 0);
        $country = (array_key_exists("country", $_GET) ? $_GET['country'] : "GB");

        var_dump(search_address($_GET['input'], $country, $page));
        
    } else {
        
        echo "<p>Pass a postcode or part of and address using <code>?input=nr147pz</code></p>";
        
    }

    function search_address($input = "", $country_code = "GB", $page = 0) {
        
        // Replace with your API key, test key test key is locked to NR14 7PZ postcode search
        $api_key = "PCW45-12345-12345-1234X";
        
        // Grab the input text and trim any whitespace
        $input = trim($input);
        
        // Create an empty output object
        $output = new StdClass();
        
        if ($input == "") {
            
            // Respond without calling API if no input supplied
            $output->error_message = "No input supplied";
            
        } else {
            
            // Create the URL to API including API key and encoded address
            $address_url = "https://ws.postcoder.com/pcw/" . $api_key . "/address/".$country_code."/" . urlencode($input) . "?page=" . $page; 
            
            // use cURL to send the request and get the output
            $session = curl_init($address_url); 
            // Tell cURL to return the request data
            curl_setopt($session, CURLOPT_RETURNTRANSFER, true); 
            // use application/json to specify json return values, the default is XML.
            $headers = array('Content-Type: application/json');
            curl_setopt($session, CURLOPT_HTTPHEADER, $headers);

            // Execute cURL on the session handle
            $response = curl_exec($session);
            
            $http_status_code = curl_getinfo($session, CURLINFO_HTTP_CODE);

            // Close the cURL session
            curl_close($session);
            
            if ($http_status_code != 200) {
                
                // Triggered if API does not return 200 HTTP code
                // More info - https://developers.alliescomputing.com/postcoder-web-api/error-handling
                
                // Here we will output a basic message with HTTP code
                $output->error_message = "An error occurred - " . $http_status_code;
                
            } else {
                
                // Convert JSON into an object
                $result = json_decode($response);
                
                if(count($result) > 0) {
                    
                    // Check for the morevalues element in last address
                    $last_address = end($result);
                    
                    if(property_exists($last_address, "morevalues")) {
                        
                        // Pass through the paging info when needed
                        $output->next_page = (int) $last_address->nextpage;
                        $output->num_of_addresses = (int) $last_address->totalresults;
                        
                    } else {
                     
                        $output->num_of_addresses = count($result);
                        
                    }
                    
                    $output->current_page = (int) $page;
                    
                    // Output the list of addresses
                    $output->addresses = $result;
                    
                } else {
                    
                    $output->error_message = "No addresses found";
                    
                }
                
            }
            
        }
            
        return $output;
        
    }

?>
