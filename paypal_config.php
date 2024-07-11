<?php
// File: paypal_config.php

return [
    'client_id' => 'AZOUcsxOY35JD_1Fs-FESIH_kCiR-t4zvhOwOUog1UBLOcISaSZW5BQJIdOiGOGMASznaA_AZgaiWkhA',
    'client_secret' => 'ELnRdAJkQvbCo8YFhsWcCc4teFAsFKzKuCnJPepSFbZDyK1fNxBtG38k9UfLbcFE1tOUzLmQATTnmz-L',
    'settings' => [
        'mode' => 'live', // Use 'sandbox' for testing
        'http.ConnectionTimeOut' => 30,
        'log.LogEnabled' => true,
        'log.FileName' => __DIR__ . '/PayPal.log',
        'log.LogLevel' => 'FINE'
    ]
];