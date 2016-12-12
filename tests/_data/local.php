<?php
$server = new SoapServer('EETServiceSOAP_local.wsdl');
$server->handle();
