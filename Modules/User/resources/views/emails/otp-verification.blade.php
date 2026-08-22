<x-mail::message>
# Verify Your Email

Hi {{ $name }},

Thank you for registering with **HajurKoAfno**. To complete your registration, please use the following verification code:

<x-mail::panel>
<div style="text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 5px;">
{{ $code }}
</div>
</x-mail::panel>

This code expires at **{{ $expiresAt }}**. Please enter this code on the verification page to complete your registration.

**Security Warning:** Please do not share this OTP with anyone. Our support team will never ask for your verification code.

If you didn't request this code or did not attempt to register an account, you can safely ignore this email.

For any assistance, please contact our support at [support@hajurkoafno.com](mailto:support@hajurkoafno.com).

Thanks,<br>
**HajurKoAfno Team**

<x-slot:subcopy>
You're receiving this email because a registration attempt was made using your address.<br>
[Privacy Policy](#) | [Terms of Service](#) | [Contact Us](#)
</x-slot:subcopy>
</x-mail::message>
