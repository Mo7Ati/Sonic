<?php

return [

    'phone_verification' => [
        'otp_message' => 'Your :app verification code is :code. Valid for :minutes minutes.',
        'expired' => 'This verification code has expired. Please register again.',
        'invalid' => 'The verification code is incorrect.',
        'too_many_attempts' => 'Too many incorrect attempts. Please register again.',
        'not_found' => 'No pending verification found for this phone number.',
        'resend_cooldown' => 'Please wait before requesting another code.',
        'sent' => 'Verification code sent to your WhatsApp.',
        'resent' => 'Verification code resent to your WhatsApp.',
        'verified' => 'Phone verified successfully. Registration complete.',
    ],

];
