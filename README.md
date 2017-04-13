# WO-RDS
Takes now playing data from WideOrbit Automation for Radio and sends it to multiple locations. Designed to run on a Raspberry Pi

# rds.py
This file starts the TCP Server on whatever desired port you want. Once RDS data is received, we sent it to a remote server and also start a subprocess to send via UDP to our Inovonics RDS Encoder.

# inovonics.php
This file creates the UDP Connection to the RDS Encoder
