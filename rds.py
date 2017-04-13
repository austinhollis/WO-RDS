import socket, select, requests, json, subprocess
  
if __name__ == "__main__":
      
    CONNECTION_LIST = []
    RECV_BUFFER = 4096 # Advisable to keep it as an exponent of 2
    PORT = 9150 
         
    server_socket = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    server_socket.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
    server_socket.bind(("0.0.0.0", PORT))
    server_socket.listen(10)
 
    # Add server socket to the list of readable connections
    CONNECTION_LIST.append(server_socket)
 
    print "RDS Updater started on port " + str(PORT)
 
    while 1:
        # Get the list sockets which are ready to be read through select
        read_sockets,write_sockets,error_sockets = select.select(CONNECTION_LIST,[],[])
 
        for sock in read_sockets:
             
            #New connection
            if sock == server_socket:
                # Handle the case in which there is a new connection recieved through server_socket
                sockfd, addr = server_socket.accept()
                CONNECTION_LIST.append(sockfd)
                print "Client/Automation System (%s, %s) connected" % addr
                 
            #Some incoming message from WideOrbit
            else:
                try:
                    data = sock.recv(RECV_BUFFER)
                    # echo back the client message
                    if data:
                        sock.send('OK ... ' + data)
                        print "incoming message" # NEW RDS DATA INBOUND
                        print data
                        print "init api call"
                        # we post the data to our API and save the data in a database
                        callAPI = "API URL GOES HERE"
                        apiRequest = requests.post(callAPI, data)
                        print apiRequest.json
                        print "posted to api"
                        print "starting subprocesses" # now we have a php script that sends the data to our Inovonics 730 RDS encoder
                        subprocess.call(["php", "/var/www/html/inovonics.php"])
                        print "subprocess completed"
                        print "ready for next track"
                        print "=========================="
                 
                # client disconnected, so remove from socket list
                except:
                    broadcast_data(sock, "Client (%s, %s) is offline" % addr)
                    print "Client (%s, %s) is offline" % addr
                    sock.close()
                    CONNECTION_LIST.remove(sock)
                    continue
         
    server_socket.close()
