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

    'phone_change' => [
        'same_phone' => 'This is already your phone number.',
        'already_taken' => 'This phone number is already registered to another account.',
        'sent' => 'Verification code sent to your new phone number.',
        'resent' => 'Verification code resent to your new phone number.',
        'verified' => 'Phone number updated successfully.',
    ],

];
