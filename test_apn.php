<?php

// NOTE: not upgrading this 
 
    checkAppleAPNS();
    
    //checkFeedbackServer ($appBundle, TRUE, 'iGlooPush666');
 
    function checkAppleAPNS ()
    {
        $pemFileName = 'iGlooPush_Developer.pem';
        //$pemFileName = 'iGlooPushPublic.pem';
        $passphrase  = 'iGlooPush666';
        //$passphrase  = 'x';

    	$ctx = stream_context_create();
	stream_context_set_option($ctx, 'ssl', 'local_cert', $pemFileName);
	stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

	// Open a connection to the APNS server

	//$fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $error, $errorString, 60, STREAM_CLIENT_CONNECT, $ctx);
	$fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $error, $errorString, 60, STREAM_CLIENT_CONNECT, $ctx);

	if (!$fp)
	{
    	    //return "Failed to iOS connect error: $err $errstr".PHP_EOL;
    	    print "Failed to connect ($error) $errorString";
    	    //exit("Failed to connect: $err $errstr" . PHP_EOL);
        }

    }
 
    function checkFeedbackServer ($appBundle, $useDev = TRUE, $passphrase)
    {
        $apnsPort = 2195;
        //$apnsCert = keyForApp ($appBundle, $useDev);

        if($useDev)
        {
            echo 'FEEDBACK in DEVELOPER MODE <br/>';
            $apnsHost = 'feedback.sandbox.push.apple.com';
        }
        else
        {
            echo 'FEEDBACK in DISTRIBUTION MODE <br/>';
            $apnsHost = 'feedback.push.apple.com';
        }
        $finalPath = 'ssl://' . $apnsHost . ':' . $apnsPort;

        echo 'OPENING STREAM TO -> ' . $finalPath . '<br/>';
        echo 'USING CERT : ' . $apnsCert . "<br/>";


        $stream_context = stream_context_create();
        //stream_context_set_option($stream_context, 'ssl', 'local_cert', $apnsCert);
        //stream_context_set_option($stream_context, 'ssl', 'passphrase', $passphrase);
        
        $pemFileName = 'iGlooPush_Developer.pem';
       
        stream_context_set_option($stream_context, 'ssl', 'local_cert', $pemFileName);
        stream_context_set_option($stream_context, 'ssl', 'passphrase', $passphrase);
        
        //$apns = stream_socket_client($finalPath, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $stream_context);
        $apns = stream_socket_client ('ssl://gateway.sandbox.push.apple.com:2195', $error, $errorString, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $stream_context);
        
        if(!$apns) 
        {
            echo "ERROR $errcode: $errstr\n";
            return;
        }
        else echo 'APNS FEEDBACK CONNECTION ESTABLISHED...<br/>';

        $feedback_tokens = array();    
        $count = 0;

        echo 'error= ' . $error . '<br/>';
        echo 'errorString= ' . $errorString . '<br/>';

        if(!feof($apns))
            echo 'APNS NOT FINISHED <br/>';
        else
            echo 'APNS FINISHED? <br/>';    

        $result = fread($apns, 38);
        echo 'result= ' . $result;
        fclose($apns);
    }

?>
