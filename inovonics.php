<?php
$servername = "";
$username = "";
$password = "";
$dbname = "";

$rdsIP=""; //The IP Address of your Inovonics RDS Encoder

//Since we sent the data to our API on a remote server, we are going to call that data back
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
$sql = "SELECT * FROM RADIOHISTORY ORDER BY ID DESC LIMIT 1";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $currentTitle=$row['TITLE'];
        $currentArtist=$row['ARTIST'];
    }
} else {
    echo "0 results";
}
$conn->close();

//For RDS, we need to know the number of characters
$titleLength=strlen($currentTitle);
$artistLength=strlen($currentArtist);

$titleLength=$titleLength-1;
$artistLength=$artistLength-1;

$rtpArtistStart=$titleLength+4;

$rtpVars="01,00,".$titleLength.",04,".$rtpArtistStart.",".$artistLength;

$totalLength=$titleLength+$artistLength+5;
echo $totalLength;
echo "<br/>";
echo $titleLength." ".$artistLength."<br/>";
echo $currentTitle." - ".$currentArtist;
echo "<br/>";
echo $rtpVars;

// We cannot have a total length over 64 characters
if($totalLength>64){
	exit();
}

/*
We are settings the Dynamic PS
128 (max) characters for scrolling messages in the PS field
*/

$ip = $rdsIP; // Inovonics IP Address
$port = 10005; // We use the default UDP Port
$str = "DPS=".$currentTitle." by ".$currentArtist." on Victory 91.5\r\n";
$str2 = "TEXT=".$currentTitle." - ".$currentArtist."\r\n";
$str3 = "RTP=".$rtpVars."\r\n";

$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP); 
socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1);
socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>5, "usec"=>0));

socket_sendto($sock, $str, strlen($str), 0, $ip, $port);

while(true) {
  $ret = @socket_recvfrom($sock, $buf, 20, 0, $ip, $port);
  if($ret === false) break;
  echo "Message : < $buf > , $ip : $port <br>";
}
socket_close($sock);

/*
This will set the Radio Text
64 (max) character message to be displayed by the receiver if so equipped
*/

$ip = $rdsIP;
$port = 10005;
$str2 = "TEXT=".$currentTitle." - ".$currentArtist."\r\n";
$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP); 
socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1);
socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>5, "usec"=>0));
socket_sendto($sock, $str2, strlen($str2), 0, $ip, $port);
while(true) {
  $ret = @socket_recvfrom($sock, $buf, 20, 0, $ip, $port);
  if($ret === false) break;
  echo "Message : < $buf > , $ip : $port <br>";
}
socket_close($sock);

/*
Finally, setting the RT+ Tagging Data
RTP refers to "RT+" the RDS feature that enables song tagging and other 'interactive' applications. This menu shows the numerical value of RT+ data
packets as they are received and queued for transmission
*/

$ip = $rdsIP;
$port = 10005;
$str3 = "RTP=".$rtpVars."\r\n";
$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP); 
socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1);
socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>5, "usec"=>0));
socket_sendto($sock, $str3, strlen($str3), 0, $ip, $port);
while(true) {
  $ret = @socket_recvfrom($sock, $buf, 20, 0, $ip, $port);
  if($ret === false) break;
  echo "Message : < $buf > , $ip : $port <br>";
}
socket_close($sock);
?>
