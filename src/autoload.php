<?php
/**
 * include all required files
 */
require_once 'Certificate.php';
require_once 'ClientException.php';
require_once 'Config.php';
require_once 'Connector.php';
require_once 'Receipt.php';
require_once 'ServerException.php';
require_once 'ServerWarning.php';
require_once 'Signer.php';
require_once 'SoapClient.php';

require_once '../vendor/robrichards/wse-php/src/WSASoap.php';
require_once '../vendor/robrichards/wse-php/src/WSSESoap.php';
require_once '../vendor/robrichards/wse-php/src/WSSESoapServer.php';
require_once '../vendor/robrichards/xmlseclibs/src/XMLSecEnc.php';
require_once '../vendor/robrichards/xmlseclibs/src/XMLSecurityDSig.php';
require_once '../vendor/robrichards/xmlseclibs/src/XMLSecurityKey.php';
