# IdP ID Sync
Tool to synchronize user records between the ID Broker and an ID Store

## Configuration files
Copy ```local.env.dist``` to ```local.env``` and supply any necessary values.

## Testing

### Run all except integration tests

Run `make testci`

### Integration tests

Some additional setup is required to run integration tests:

#### Google config

- Create a Google Sheets using the `google-sheets.csv` template file
- Copy sheet ID from the browser address bar. It's the long string of characters after `/d/` and before `/edit`
- Save sheet ID in `local.env` as `TEST_GOOGLE_SHEETS_CONFIG_spreadsheetId`
- Set `TEST_GOOGLE_SHEETS_EMPLOYEE_ID`  in `local.env` to `1234567`
- Set `TEST_GOOGLE_SHEETS_CONFIG_applicationName`  in `local.env` to `id-sync`
- Create Google auth token (TBD: include or link to instructions)
- Save token in `application/google-auth.json` and set `TEST_GOOGLE_SHEETS_CONFIG_jsonAuthFilePath` to `/data/google-auth.json`

#### Workday config

Unless you have access to a test-only Workday account, you will need a valid employee ID and valid Workday credentials.
Set the `TEST_WORKDAY_CONFIG_*****` variables in `local.env` using `local.env.dist` as an example.

#### Sage People config

Unless you have access to a test-only Sage account, you will need a valid employee ID and valid Sage credentials. 
Set the `TEST_SAGE_PEOPLE_CONFIG_*****` variables in `local.env` using `local.env.dist` as an example.

## User properties

### Employee Number (`employee_number`)
This is the primary key that uniquely identifies each user record. It can consist of any alph-numeric characters. Required.

### First Name (`first_name`)
A user’s first name. Required.

### Last Name (`last_name`)
A user’s last name.

### Display Name (`display_name`)
A user’s full name. If blank, the display name will be the first name and last name concatenated.

### Email (`email`)
A user’s primary email address. The user must have a primary email address. The `ALLOW_EMPTY_EMAIL` configuration option can be set to `true` to temporarily allow a user to only have a personal email address (see below). This can be useful if the onboarding process does not allow for an organizational email address to be created before the IdP user is added.

### Username (`username`)
A user’s username. The user can use either their username or their email address on login. Required.

### Account Locked, Disabled or Expired (`locked`)
Flag to identify a temporarily disabled account. Must be one of: ‘yes’, ‘true’, 1, ‘no’, ‘false’, 0. Note that this is in addition to the “active” property that is implicitly set (or cleared) by the user’s inclusion in (or later exclusion from) the list of users provided to the IdP.

### Require MFA (`requireMfa`)
Flag to enforce the use of multi-factor authentication. Must be one of: ‘yes’, ‘true’, 1, ‘no’, ‘false’, 0

### Manager Email (`manager_email`)
Email address of the user’s manager. This can be used in a situation where a user loses access to their multi-factor authentication and needs assistance. In such a situation, they can request assistance from their manager, who will receive a temporary code that can be given to the user once they have positively identified the person.

### Personal Email (`personal_email`)
Alternate email address of the user. Upon creation of a new user, if specified, this address will be added as a password recovery option. May also be used temporarily as a new user's primary email address if configuration option `ALLOW_EMPTY_EMAIL` is `true`.

### Groups (`groups`)
A comma-separated list of groups that can be used to limit access to certain applications (SAML Service Providers) based on SAML configuration elsewhere in the IdP system.
