# Bypass 2-Factor Authentication
This module is designed to allow developers to bypass the need for 2-factor authentication (2FA) in local development environments.

### Configuration
The bypassing of 2FA is made possible for both admin access and the generation of admin tokens via API.
Both methods of access are controlled by setting variables in the environment's `app/etc/env.php` file:
 - `BYPASS_2FA_ADMIN` when set to `true`, this variable allows bypassing of 2FA for admin actions.
 - `BYPASS_2FA_API` when set to `true`, this variable allows bypassing of 2FA for admin token generation.

Additionally, there is an added security measure, to prevent 2FA being bypassed accidentally (or intentionally) in production environments.
The `app/etc/config.php` file must have the following config setting added under `system => default`:
```
'system' => [
  'default' => [
    'bypass_2fa' => [
      'settings' => [
        'allowed_hostnames' => [
          'hostname1',
          'hostname2',
          ...
        ]
      ]
    ]
  ]
]
```
The configured (partial) hostnames are checked against the base URL, with the configured name needing to be contained wholly within the base URL.
If no match is found, then 2FA is not bypassed for the request.
By committing this setting to the codebase, it prevents someone from overriding the allowed hosts in environments such as Magento Cloud hosting (where `app/etc/config.php` is not writable).