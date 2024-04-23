<?php 
        function fcmnotify($token,$notification){
        $getFcmKey = '###';
        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
        

        $extraNotificationData = ["message" => $notification];

        $fcmNotification = [
            'to'        => $token,
            'notification' => $notification,
            'data' => $extraNotificationData,
        ];

        $headers = [
            'Authorization: key=' .$getFcmKey,
            'Content-Type: application/json'
        ];

        

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
        
        $response = curl_exec($ch);
        
        
        curl_close($ch);
    }