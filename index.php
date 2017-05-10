 <?php
    $url = $_GET['link'];//api link or $url = "https://backend-challenge-fall-2017.herokuapp.com/orders.json" ;
    $cookie;
    $json = json_decode(file_get_contents($url));

    if(is_null($json) || $json == null)
    {
        $response["message"] = "Empty response array";
    }
    else{

        $cookies_count =  $json->{"available_cookies"};
        $orders        =  $json->{"orders"};
        $per_page      =  $json->{"pagination"}->{"per_page"};
        $total         =  $json->{"pagination"}->{"total"};

        if($total % $per_page != 0)
        {
            $counter = ((int)($total/$per_page))+1;
        }
        else{
            $counter = $total/$per_page;
        }

        $flag = 1;
        do {
                foreach ($orders as $order)
                {
                    foreach ($order->{"products"} as $product)
                    {
                        if(strcmp(($product->{"title"}),"Cookie") ==0)
                        {
                            $cookie_order[$order->{"id"}] = $product->{"amount"};
                        }
                    }
                }
                if($counter >1)
                {
                    $flag = $flag+1;
                    $json = json_decode(file_get_contents($url."?page=".$flag));
                    $orders = $json->{"orders"};
                }
                $counter = $counter-1;
           }
        while ($counter != 0);
        // Sorting the array first by value in desc then by key in asc
        $keys   =  array_keys($cookie_order);
        $values =  array_values($cookie_order);
        array_multisort($values, SORT_DESC, $keys,SORT_ASC, $cookie_order);
        $cookie_order = array_combine($keys,$values);

        $previous = 0;
        foreach($cookie_order as $order_key=> $product_amount )
        {
            if($product_amount <=$cookies_count)
            {
               $cookies_count = $cookies_count - $product_amount;
            }
            else
            {
                $unprocessed[] = $order_key;
            }
        }
		$unprocessed = is_null( $unprocessed)?0: $unprocessed;
		sort($unprocessed);
        $response["remaining_cookies"]   = $cookies_count;
        $response["unfulfilled_orders"]  = $unprocessed;
    }
   echo  json_encode($response);
?>