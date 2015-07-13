<?php 
//
$a = @$_SERVER['SERVER_ADDR'];
$host = $a;
$port = 4141;

// trying to create a socket, stops the process ( DIE ) if socket not created

if(!($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) )){
	$errcode = socket_last_error();
	$errormsg = socket_strerror($errcode);
	die("couldn't create socket [$errcode]:$errormsg");
}
echo "successfully created the socket \n";

// trying to bind the socket to an ip:port or DIE

if(!socket_bind($sock,$host,$port)){
   $errcode = socket_last_error();
	$errormsg = socket_strerror($errcode);
	die("couldn't bind socket to ip [$errcode]:$errormsg");	
}

echo "successfully binded the socket to given ip\n";


// socket listening for incoming connections

if(!socket_listen($sock,10)){
	$errcode = socket_last_error();
	$errormsg = socket_strerror($errcode);
	die("socket is deaf  [$errcode]:$errormsg");  // if socket not successful to listen for connections
}

echo "socket started listening\n";
echo "waiting for incoming connections...\n";

$clients = array($sock);
$address_clients = array();

//start endless loop, so that our script doesn't stop
while (true) {
	//manage multiple connections
	$changed = $clients;
    
	//returns the socket resources in $changed array
    if(count($changed)>0) socket_select($changed, $null, $null, 0);
	
    //check for new socket
	if (in_array($sock, $changed)) {
		$socket_new = socket_accept($sock);                        //accept new socket
		$clients[] = $socket_new;                                  //add socket to client array
		$found_socket = array_search($socket_new, $clients);
		$header = socket_read($socket_new,1024);                   //read data sent by the socket
		perform_handshaking($header, $socket_new, $host, $port);   //perform websocket handshake
        
		socket_getpeername($socket_new, $address,$port);           //get ip address of connected socket
		$address_clients[$found_socket] = $address;
		$found_socket = array_search($sock, $changed);

		echo "Client[$address] connected to us on port $port\n";
        //make room for new socket
		unset($changed[$found_socket]);
	}
    
	//loop through all connected sockets
	foreach($changed as $changed_socket){	
		//check for any incomming data
		while(socket_recv($changed_socket, $buf, 1024, 0) >= 1)
		{
			$received_text = unmask($buf); //unmask data
			$received = explode(',', $received_text);
			
            if(!empty($received[1])){
                    echo $received[0]."\t".$received[1]."\n";
                if(array_search($received[0], $address_clients)){
                    $found_socket = array_search($received[0], $address_clients);
                    socket_write($clients[$found_socket], mask($received[1]),strlen(mask($received[1])));
                }
             }
            break 2; //exit this loop
		}
		
		$buf = socket_read($changed_socket, 1024, PHP_NORMAL_READ);
		if ($buf === false){
            // remove client for $clients array
			$found_socket = array_search($changed_socket, $clients);
			socket_getpeername($clients[$found_socket], $address);
			unset($clients[$found_socket]);
			
			echo "Client[$address] disconnected\n";
		}
	}
}

function perform_handshaking($receved_header,$client_conn, $host, $port){
	$headers = array();
	$lines = preg_split("/\r\n/", $receved_header);
	foreach($lines as $line)
	{
		$line = chop($line);
		if(preg_match('/\A(\S+): (.*)\z/', $line, $matches))
		{
			$headers[$matches[1]] = $matches[2];
		}
	}

	$secKey = $headers['Sec-WebSocket-Key'];
	$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
	//hand shaking header
	$upgrade  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
	"Upgrade: websocket\r\n" .
	"Connection: Upgrade\r\n" .
	"WebSocket-Origin: $host\r\n" .
	"WebSocket-Location: ws://$host:$port/\r\n".
	"Sec-WebSocket-Accept:$secAccept\r\n\r\n";
	socket_write($client_conn,$upgrade,strlen($upgrade));
    return $upgrade;
}

function unmask($text) {
	$length = ord($text[1]) & 127;
	if($length == 126) {
		$masks = substr($text, 4, 4);
		$data = substr($text, 8);
	}
	elseif($length == 127) {
		$masks = substr($text, 10, 4);
		$data = substr($text, 14);
	}
	else {
		$masks = substr($text, 2, 4);
		$data = substr($text, 6);
	}
	$text = "";
	for ($i = 0; $i < strlen($data); ++$i) {
		$text .= $data[$i] ^ $masks[$i%4];
	}
	return $text;
}

//Encode message for transfer to client.
function mask($text)
{
	$b1 = 0x80 | (0x1 & 0x0f);
	$length = strlen($text);
	
	if($length <= 125)
		$header = pack('CC', $b1, $length);
	elseif($length > 125 && $length < 65536)
		$header = pack('CCn', $b1, 126, $length);
	elseif($length >= 65536)
		$header = pack('CCNN', $b1, 127, $length);
	return $header.$text;
}

?>