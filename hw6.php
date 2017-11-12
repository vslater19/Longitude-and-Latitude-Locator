<!DOCTYPE html>
<!-- Made by: Nikolas and Victoria

    Participation Matrix:

                              Nik's Contribution | Victoria's Contribution
                              -----------------------------------------
           Nik's Assessment:  |       50%        |         50%
                              -----------------------------------------
      Victoria's Assessment:  |       50%        |         50%
      -->
<html>
<head> <link rel="stylesheet" type="text/css" href="hw6.css">
    
    <script type="text/javascript">
        
        function readOnly(){
            document.getElementById("read").readOnly = true;
            document.getElementById("read1").readOnly = true;
        }
        
        function draw() {
			var canv=document.getElementById("myCanvas");
            canv.width = 640;
            canv.height = 330;
            canv.textAlign = "center";
            canv.textBaseline = "middle";
            
			var c=canv.getContext("2d");
            c.scale = (1, 1);

			var img = new Image();
            img.src = "https://maps.googleapis.com/maps/api/staticmap?center=38.3,+-97.0&zoom=4&scale=1&size=640x440&maptype=roadmap&format=png";
            img.scale = (3, 1);
  
            
			img.onload = function(){
                // resize the canvas to the new image size
                c.drawImage(img, 0, 45, 640, 440, 100, 0, 600, 413);
                drawMarker(sessionStorage.getItem("xPos"), sessionStorage.getItem("yPos"));
			}
		}
    </script>
</head>
<body onload = "readOnly(), draw()">
    <br>
    <br>
    <br>

    <div id = "headerBack"> 
        <div id = "header"> <h1> THE COM214 ZIP CODE LOCATOR 
            <div id = "buttonsDB"> 
                <form action = "hw6.php" method = "get">
                <input class = "Button"  type="submit" name = "createDB" value = "Create DB">
                <input class = "Button"  type="submit"  name = "dropDB" value = "Drop DB" >
             </form> </div>
       </h1>
        </div></div>  

    <div id = "mapBox">
     <br>
          <canvas id="myCanvas">
            Your browser does not support the canvas element.
          </canvas>
  
    </div>
    
    <div id = "headerBack"> 
        <div id= "latlon"> 
            <form action = "hw6.php" method = "get">
                LATITUDE: <input type = "number" id = "read" name = "lat" maxlength = "7" > 
                LONGITUDE: <input type = "number" id = "read1" name = "lon" maxlength = "7" size = "4">
                <input class="Button" type="submit" name= "listZip" value="List Nearby Zip Codes" style="font:bold 16px verdana, sans-serif;">
                Items per page   
                <select id = "selection" type=number name="lim">
                    <option value="1" name="1">1</option>
                    <option value="2" name="1">2</option>
                    <option value="3" name="1">3</option>
                    <option value="4" name="1">4</option>
                    <option value="5" name="1">5</option>
                    <option value="6" name="1">6</option>
                    <option value="7" name="1">7</option>
                    <option value="8" name="1">8</option>
                    <option value="9" name="1">9</option>
                    <option value="10" name="1">10</option>
                </select>    
            </form>
        </div>
        
        <div id = "headerBack1"> 
            
            <?php
                // --- CREATE THE DATABASE
                if (isset($_GET["createDB"])) {
                    $db_conn = mysqli_connect("localhost", "root", "");
                    if (!$db_conn)
                        die("Unable to connect: " . mysqli_connect_error());

                    mysqli_query($db_conn, "CREATE DATABASE IF NOT EXISTS zips;");

                    // --- CREATE THE TABLE
                    mysqli_select_db($db_conn, "zips");
                    $cmd = "CREATE TABLE clist (zip int(6) NOT NULL PRIMARY KEY, 
                                                city varchar(20), 
                                                state varchar(2), 
                                                lat float(6,4), 
                                                lon float(7,4), 
                                                zone int(1) );";
                    mysqli_query($db_conn, $cmd);

                    $cmd = "LOAD DATA LOCAL INFILE 'zip_codes_usa.csv' INTO TABLE clist FIELDS TERMINATED BY ',';"; 
                    //MAC users reading files created on windows might need to add: LINES TERMINATED BY '\r\n'
                    mysqli_query($db_conn, $cmd);
                    echo ('Database created');
                    mysqli_close($db_conn);
                }

                // --- Fill the table from the database
                if (isset ($_GET["listZip"])) {
                    $db_conn = mysqli_connect("localhost", "root", ""); // open connection
                    if (!$db_conn)
                        die("Unable to connect: " . mysqli_connect_error());  // die is similar to exit

                    $retval = mysqli_select_db($db_conn, "zips"); 

                        // --- FILL THE ZIP TABLE
                        if ($retval){
                            //get lat and long and make as var $inLat and $inLon and the number to display as $lim
                            if (!empty($_GET["lat"]) && !empty($_GET["lon"])) {
                                $inLat = $_GET["lat"];
                                $inLon = $_GET["lon"];
                                $lim = $_GET["lim"];


                                echo ("<table> <tr align = center> <th align = center>Zip Code</th> <th align = center>City</th> <th align = center>State</th> <th align = center>Lat</th> <th align = center>Lon</th> <th align = center>Distance(miles)</th> <th align = center>Time Diff(ET)</th> </tr>" . PHP_EOL);
                                $cmd = "SELECT * ,
                                                SQRT(POW(ABS($inLat - Lat),2) + POW(ABS($inLon - Lon), 2)) AS dist
                                                FROM clist ORDER BY dist limit $lim;";
                                $records = mysqli_query($db_conn, $cmd);
                                while($row = mysqli_fetch_array($records)){
                                    $distance = latLonToMiles($row["lat"], $row["lon"], $inLat, $inLon);
                                    echo("<tr> <td>" .$row['zip']."</td><td>" .$row['city']."</td><td>" .$row['state']."</td><td>" .$row['lat']."</td><td>" .$row['lon']."</td><td>" .number_format($distance, 2, '.', '')."</td><td>" .($row['zone'] +5)."</td><td> </tr>" . PHP_EOL);
                                }
                                echo ("</table." . PHP_EOL);
                            }  
                        }
                        else{
                            echo("No database present. Please create the database.");
                        }
                    mysqli_close($db_conn);
                }
            
                // --- Drop the database
                if (isset($_GET["dropDB"])) {
                    $db_conn = mysqli_connect("localhost", "root", "");
                    if (!$db_conn)
                        die("Unable to connect: " . mysqli_connect_error());  // die is similar to exit

                    $retval = mysqli_query($db_conn , "DROP DATABASE zips;");
                    if(!$retval )
                        die('No database to delete');

                    echo ("Database deleted");
                    mysqli_close($db_conn);
                }
            
                function latLonToMiles($lat1, $lon1, $lat2, $lon2){  //Haversine formula
                    $R = 3961;  // radius of the Earth in miles
                    $dlon = ($lon2 - $lon1)*M_PI/180;
                    $dlat = ($lat2 - $lat1)*M_PI/180;
                    $lat1 *= M_PI/180;
                    $lat2 *= M_PI/180;
                    $a = pow(sin($dlat/2),2) + cos($lat1) * cos($lat2) * pow(sin($dlon/2),2) ;
                    $c = 2 * atan2( sqrt($a), sqrt(1-$a) ) ;
                    $d = $R * $c;
                    return $d;
                }
                ?>
        </div>
        
    </div>
   
    <script>
        function getMousePos(canvas, events){
  		    var obj = canvas;
  		    var top = 0, left = 0;
			var mX = 0, mY = 0;
 			while (obj && obj.tagName != 'BODY') { //accumulate offsets up to 'BODY'
                top += obj.offsetTop;
                left += obj.offsetLeft;
                obj = obj.offsetParent;
            }
            mX = events.clientX - left + window.pageXOffset;
            mY = events.clientY - top + window.pageYOffset;
            return { x: mX, y: mY };
		}

		window.onload = function(){
           draw();
    	   var canvas = document.getElementById('myCanvas');
    	   canvas.addEventListener('mousedown', function(events){
                var mousePos = getMousePos(canvas, events);
                fillLatLon(mousePos.x, mousePos.y);
                sessionStorage.setItem("xPos", mousePos.x);
                sessionStorage.setItem("yPos", mousePos.y);
               
                draw();
			 });

            document.getElementById("read").readOnly = true;
            document.getElementById("read1").readOnly = true;
            
            if (sessionStorage.getItem("xPos") == null){
                sessionStorage.setItem("xPos", 300);
                sessionStorage.setItem("yPos", 115);
                sessionStorage.setItem("lim", 5);
            }
            fillLatLon(sessionStorage.getItem("xPos"), sessionStorage.getItem("yPos"));
            document.getElementById("selection").value = sessionStorage.getItem("lim");
            
		}
        
        window.onbeforeunload = function(){
            var val = document.getElementById("selection").value;
            sessionStorage.setItem("lim", val);
        }
        
        function drawMarker(x, y){
            canv=document.getElementById("myCanvas");
            var c=canv.getContext("2d");
            c.beginPath();
            c.arc(x, y, 15, 0, Math.PI*2);
            c.strokeStyle="darkblue";
            c.fillStyle="rgba(255,255,255,.5)";
            c.stroke();
            c.fill();
        }
        
        function fillLatLon(x, y){
            var lat = document.getElementById("read");
            var long = document.getElementById("read1");
            lat.value = YtoLat(y).toFixed(4);
            long.value = XtoLong(x).toFixed(4);
        }
        
        function XtoLong(x){
            return (x*(43.078483/459))-125.0949450022;
        }
      
        function YtoLat(y){
            return (y*(-5.900365/80))+50.27823875;
        }
    </script>
    
</body>
</html>
