# Skyword API

## Drupal 7 Setup

### I. Module setup

1. Install Drupal like you normally install it.
2. Download these modules `services`, `oauth2_server`, `oauth2_client`, `oauth2_authentication`.
3. Go to modules administration page and enable the following.
   `Services`, `Rest Server`, `OAuth2 Server`, `OAuth2 Authentication`, `OAuth2 Client and Test`.
3. Finally enable the `Skyword` module.

### II. Add a service

1. Go to `admin/structure/services` and add a services.
2. Give the machine name *skyword_services*.
3. Select the server as *REST*.
4. For the "path to endpoint", enter `skyword/publish/v1/oauth2`.
5. Choose the authentication type as *OAuth2 authentication*.
6. Save

### III. Add resources

1. Go to `/admin/structure/services/list/skyword_services/resources`.
2. *FOR NOW* enable *token*, *version* and *taxonomies*.
3. Click Save.

### IV. Create 0Auth2 Server

1. Go to `admin/structure/oauth2-servers` and create a new 0Auth2 server.
2. On the label, put `Skyword`.
3. Check the following settings `Allow the implicit flow`, `Authorization code`, `Client credentials`.
4. On advance settings, input *3600* for the access token lifetime.
5. Save the server.

### V. Add OAuth2 Authentication

1. This is for the service you created earlier. Go to `admin/structure/services/list/skyword_services/authentication`.
2. From the select box, choose `Skyword`.
3. Save.

### VI. Server Responses.

1. Again, this is for the service you created earlier. Go to `admin/structure/services/list/skyword_services/server`.
2. For the response formatter, choose only `json`.
3. From the request parsing, choose what do you think is applicable.
4. Save.

## Permissions

For now, enable the following Skyword permissions for Anonymous use which is:

1. Allow to retrieve a token
2. Allow to retrieve the version
3. Allow to retrieve a list of taxonomies

## Testing the API

I am using Postman for testing the API. Postman is Chrome App which you can download from here:
[https://chrome.google.com/webstore/detail/postman/fhbjgbiflinjbdggehcddcbncdddomop?hl=en](https://chrome.google.com/webstore/detail/postman/fhbjgbiflinjbdggehcddcbncdddomop?hl=en)

Install that, and once that is installed you may import this readily made Skyword collection, ready for testing:
https://www.dropbox.com/s/atceni7p61n8j3t/Skyword.postman_collection.json?dl=0

Import that json file into Postman. Currently, there are 7 postman test you can play on there:

1. Get Authentication Token
2. Get Version
3. Get List of Taxonomies
4. Create a Taxonomy
5. Get specific Taxonomy
6. Get a list of Terms from a Taxonomy
7. Create a Term from a Taxonomy

PS. You may need to update the domain name `skyword.local` with your own domain name.
