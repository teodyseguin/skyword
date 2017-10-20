<?php
/**
 * @file
 * site_configuration.strongarm.inc
 */

/**
 * Implements hook_strongarm().
 */
function site_configuration_strongarm() {
  $export = array();

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'oauth2_authentication_class';
  $strongarm->value = 'OAuth2AuthenticationClient';
  $export['oauth2_authentication_class'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'oauth2_authentication_client_id';
  $strongarm->value = 'as732j823';
  $export['oauth2_authentication_client_id'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'oauth2_authentication_client_secret';
  $strongarm->value = 'asdfin3298n';
  $export['oauth2_authentication_client_secret'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'oauth2_authentication_scope';
  $strongarm->value = 'skyword';
  $export['oauth2_authentication_scope'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'oauth2_authentication_token_endpoint';
  $strongarm->value = 'http://www.skyword.local/skyword/publish/v1/oauth2/test';
  $export['oauth2_authentication_token_endpoint'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'oauth2_authentication_user_edit_email';
  $strongarm->value = 0;
  $export['oauth2_authentication_user_edit_email'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'oauth2_authentication_user_edit_password';
  $strongarm->value = 0;
  $export['oauth2_authentication_user_edit_password'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'oauth2_server_keys';
  $strongarm->value = array(
    'private_key' => '-----BEGIN PRIVATE KEY-----
MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQCjcbaSvAPcaWfj
/PtbmaVoeJ1+YR5j33ZHbK4zCUzdRCbuGTbcMKJGnbS33YL4Uh155NRgjwNKjYXP
O65cqhMnnO1u4aPBx7hKTH1CrRQk55BIpQY3kSp+HZCxlxSiV4Oci/jDHNkh9cOh
lEGJRldXMJhJzbcC5I9b39+oMnlI5qXLJL9YfJFpZWCVLeoSwRiQsrEWK27iCGDl
smo/a3k5GqkasGrt+AKHPAt2fVL20lAjPYog3YJ2CjVcpwBPqc5zMfabeh2FI+23
vW+ZCIR0AcyKh+5DQycRbXVHi5gPwycuQjchCqrNpDyb6UVte5z4PtAVuybDqmuJ
zP2+s645AgMBAAECggEANJRRUpUPfOmf1VU+G3KrJpeeuthNaX2q1EFfLOXyB0B0
caZE4sL2gVccmwW0q2PnPZ5iwAlh2qr6f/dIGWFRNi4ip8gI4T75x1Uh+sApOdIQ
tX35KQkXLWkfcjITHkKfAU56IXJ3W5hvbdx+1MV/NXpZk2yNUm3qvHihv781C0E8
KBM1YQp1nUDo+3RDT88p7mJFBl0JDx+nYdjkxVTpHPR4V2aASsNnApWXYNR4BF5p
GCiaQj9TSfSK1PlmOB7Xt37N5CL6J2QNfRFFgiQXVjdTlhVw+n0ZEz2fmiO2uFJk
AaUPjdb6kcxfHgXgLnG+Y/Aj4XJn54lZvRa/wsRbAQKBgQDTebeYB4/rdOx7irMJ
Et9CKPrUPww7bu7Iy8LIJn+Ylelx8SlYOQ7aviAXT6jYxv1kpFQg6fQ9yhlj98K1
QeW7CCIhkoyZdbLFU72HAqAAImXVsRHkIv7lXfaVLNtZ9p4X1u9n+eiNyBfnHs4X
lNCqBHyUvdYn7XIXN1v7ufMaYQKBgQDF2yqSIWmvAS3W0VKFJyUrtisaHaHvGP58
aQ2bAN76DaaFOlKVuIuKI85mbn8tnkllqqDd4Z2EVM7rMMw2WDLXcT+JCSj8eQ4K
RpsIr+/HQa6wu9hhetxb09oeKtikb0Ui6hOkPhaVzkCEbT+H/P+99EAHt/AmbyOv
DreFZ/eS2QKBgCjGIxvw+VLQrHf1eieZt+ottI8PyO/8p5WXk+q7YYauAOCzOuGD
vtV2KwHTnh6N33V0KbRe53SybsrTjnscRYsWOl52vx0nkFxzlOtfGjJsb24QQf7W
sIbQ4yZIC9MAteKq0mYdzTTpC5LIYbwyOTlOvCaBo7T3FzRavCWA6NqBAoGAd2tl
MnZAV4SaqkIFUKDj6pI1SboA91NaprSxpZDNBJggneEp33xXnLUn6uNel6YmHtrx
5Xo7nJejnIkDePmbFjYgZxHETz6xnrom8QNf8iBO1BVM8Ok6/6HK0NKDmBwg2oX1
/PgMobavcj6OTgICSiCWVRJMPKZwH0T+t+Kcp2ECgYAboL6BSAcGOcYKtOmCM6yY
q5+hJWnq7x/GwJUrJ+Gw/Q4zjKq1Jz+TdRjOCzkOp7iAd3/PXNB08xY+/zuOsWdz
QF2c1XwGNAVYIaOLsT+ez13dBs5sYGzOKR4GBK3fMgQLXLNuRRXKz4Tz4hx4uTsq
aagtua8JscI3wsXt50ix7A==
-----END PRIVATE KEY-----
',
    'public_key' => '-----BEGIN CERTIFICATE-----
MIIC8zCCAdugAwIBAgIBAzANBgkqhkiG9w0BAQUFADAgMR4wHAYDVQQDDBVodHRw
Oi8vc2t5d29yZC5sb2NhbC8wHhcNMTcwOTIxMjM0NDA0WhcNMTcwOTIzMjM0NDA0
WjAgMR4wHAYDVQQDDBVodHRwOi8vc2t5d29yZC5sb2NhbC8wggEiMA0GCSqGSIb3
DQEBAQUAA4IBDwAwggEKAoIBAQCjcbaSvAPcaWfj/PtbmaVoeJ1+YR5j33ZHbK4z
CUzdRCbuGTbcMKJGnbS33YL4Uh155NRgjwNKjYXPO65cqhMnnO1u4aPBx7hKTH1C
rRQk55BIpQY3kSp+HZCxlxSiV4Oci/jDHNkh9cOhlEGJRldXMJhJzbcC5I9b39+o
MnlI5qXLJL9YfJFpZWCVLeoSwRiQsrEWK27iCGDlsmo/a3k5GqkasGrt+AKHPAt2
fVL20lAjPYog3YJ2CjVcpwBPqc5zMfabeh2FI+23vW+ZCIR0AcyKh+5DQycRbXVH
i5gPwycuQjchCqrNpDyb6UVte5z4PtAVuybDqmuJzP2+s645AgMBAAGjODA2MAwG
A1UdEwEB/wQCMAAwDgYDVR0PAQH/BAQDAgeAMBYGA1UdJQEB/wQMMAoGCCsGAQUF
BwMCMA0GCSqGSIb3DQEBBQUAA4IBAQCMsmarrl+GZ18J4T2PSVdiK68xrQb4/1/i
fxUiE5FAWdn334p4gALVqlGUjs/Inm1VsY9BNYg5evzhdELW1rlIKKGF4v5za6aN
xzCPZH3M4HuNYacy24ud8qSP3uthSbhKcIdnNu2VLLuD7p6hkPtAQgoGdq5F2tyl
v3bvSg6/q8+z+xnApE6DaUxe4FstBf/1b+ycabN7hkfTqd7jU0S9Dgr1aP8p0s/u
K3l4JvkNeIhOZKibz6SQ8J+OLVkijIxvaaxLuSL9S4JWvKkMeg90q3ThfE36hsG/
Z5ZBGDs8i6Kpu/vWiBuPLO6kmbrjj8eX6Hv0iWBOyUOyEogT9gMJ
-----END CERTIFICATE-----
',
  );
  $export['oauth2_server_keys'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'oauth2_server_keys_last_generated';
  $strongarm->value = 1506037416;
  $export['oauth2_server_keys_last_generated'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'oauth2_server_next_certificate_id';
  $strongarm->value = 4;
  $export['oauth2_server_next_certificate_id'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'oauth_common_enable_provider';
  $strongarm->value = 1;
  $export['oauth_common_enable_provider'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'oauth_common_login_path';
  $strongarm->value = 'user/login';
  $export['oauth_common_login_path'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'oauth_common_request_token_lifetime';
  $strongarm->value = '3600';
  $export['oauth_common_request_token_lifetime'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'skyword_0';
  $strongarm->value = 0;
  $export['skyword_0'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'skyword_author_body';
  $strongarm->value = 'body';
  $export['skyword_author_body'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'skyword_author_byline';
  $strongarm->value = 'byline';
  $export['skyword_author_byline'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'skyword_author_content_type';
  $strongarm->value = 'author';
  $export['skyword_author_content_type'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'skyword_author_email';
  $strongarm->value = 'email';
  $export['skyword_author_email'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'skyword_author_field_mappings';
  $strongarm->value = array(
    0 => array(
      'source' => 'firstName',
      'target' => 'first_name',
    ),
    1 => array(
      'source' => 'lastName',
      'target' => 'last_name',
    ),
  );
  $export['skyword_author_field_mappings'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'skyword_author_first_name';
  $strongarm->value = 'first_name';
  $export['skyword_author_first_name'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'skyword_author_icon';
  $strongarm->value = 'icon';
  $export['skyword_author_icon'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'skyword_author_last_name';
  $strongarm->value = 'last_name';
  $export['skyword_author_last_name'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'skyword_body';
  $strongarm->value = 0;
  $export['skyword_body'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'skyword_byline';
  $strongarm->value = 0;
  $export['skyword_byline'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'skyword_email';
  $strongarm->value = 0;
  $export['skyword_email'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'skyword_first_name';
  $strongarm->value = 0;
  $export['skyword_first_name'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'skyword_icon';
  $strongarm->value = 0;
  $export['skyword_icon'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'skyword_last_name';
  $strongarm->value = 0;
  $export['skyword_last_name'] = $strongarm;

  return $export;
}
