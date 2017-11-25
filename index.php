<?php
    //SEARCH PAGE MAIN
    session_start();

    //GLOBALS
    require_once '/bing.php'; //importing BingSearch class
    $bing = new BingSearch(); //creating bing - a BingSearch class object.
    $minimum_lenght = 2; //that's minimum lenght of query to fire search action
    $results_per_page = 5; //number of results displayed on one page.
    $limit = 25; //how many results of search. Max is 50.
    $offset = 0; //default search results offset

    /*
        CHECKING IF THE GET 's' PARAMETER IS SET AND IS NOT TOO SHORT/LONG (s - stands for search query)
       AND IF IT'S NOT SAME AS LAST QUERY TO NOT PERFORM SAME SEARCH AGAIN.
    */
    if (isset($_GET['s']) && strlen($_GET['s']) >= $minimum_lenght 
    && strlen($_GET['s']) < 120 && $_SESSION['last'] !== $_GET['s'])
    {
        //SAVING RAW QUERY TO SESSION VALUE
        $_SESSION['last'] = $_GET['s'];
        //REMOVING SPECIAL CHARACTERS FROM QUERY                    
        $_SESSION['query'] = preg_replace('/[^A-Za-z0-9\-,.\'"\s]/', '', $_GET['s']);
        //AND ENCODING QUERY FOR RAWURL AGAIN    
        $query = rawurlencode($_SESSION['query']);
        //CHECK IF RESPONSE CONTAINS RESULTS IF YES SAVING TO RESPONSE
        if($res = $bing->query($query, $results_per_page, $limit))
        {
            //setting session response to temporary response
            $_SESSION['response'] = $res;
        }
        //if there is no results
        else
        {
            if(isset($_SESSION['response']))
                unset($_SESSION['response']);
        }         
        unset($res);  
    }
    //seting query to display just in case user typed not enough characters
    else if ( strlen($_GET['s']) < $minimum_lenght )
    {
        $_SESSION['query'] = preg_replace('/[^A-Za-z0-9\-,.\'"\s]/', '', $_GET['s']);
        unset($_SESSION['response']);
    }

    //CHECKING OFFSET
    if(isset($_GET['offset']) && is_numeric($_GET['offset']) && $_GET['offset'] < $limit)
    {
        $offset = $_GET['offset'];
    }
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Document</title>
    </head>

    <body>
        <div class="search-results">
            <?php
                //PRINTING RESULTS
                //check if session response is set.
                if (isset($_SESSION['response'])) {
                    //get data from response
                    $data = $_SESSION['response']['search'];
                    //print how many search results is there
                    echo '<h4>' . $data['size'] . ' result(s) found.</h4>';
                    //print each result in separate div and divide by horizontal line
                    for ($i=$offset; $i < $data['size']; $i++) 
                    { 
                        echo '<hr>';
                        echo 
                        '
                            <div><a href="'.$data['results'][$i]['url'].'"><h2>'.$data['results'][$i]['name'].'</h2></a>'
                            .$data['results'][$i]['snippet']. '</div>
                        ';
                        if(($i+1)%$results_per_page == 0) //that's stopping whenever i+1 is divisible by number of results per page that will give us correct number of results for one page
                            break;
                    }
                    //printing page number buttons
                    echo '<ul>';
                    //for amont of pages
                    for ($i=0; $i < $data['pages'] ; $i++) 
                    { 
                        //creating button
                        echo '<li><a ';
                        if($i == $offset/$results_per_page) 
                            echo 'class="active" ';
                        //set it's link to according get parameters 
                        echo 'href="/search-results?s=' . $_GET['s'] . '&offset=' . ($i*$results_per_page) . '">' . ($i+1) . '</a></li>';
                    }
                    echo '</ul>';
                }
                //if there is no results and get was fired
                else if(!isset($_SESSION['response']) && isset($_GET['s']))
                    echo '<h2>Sorry, we couldn&#39;t find any results for this search.</h2>';
                ?>
        </div>
    </body>

    </html>