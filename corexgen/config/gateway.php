<?php


return [

    'stripe' => [
        'publish_key' => env('STRIPE_PUBLIABLE_KEY','pk_test_o6ScAy3rikKa1jKhsNZ9HwLn00HIrNEESf'), // calling those from db
        'secret_key' => env('STRIPE_SECRET_KEY','sk_test_ZpiCxEOHseka5xDnfwoRoG0700L2MOuJkS'),
    ],

];