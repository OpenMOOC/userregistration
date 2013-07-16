Configuration
=============

A configuration template file is provided inside the `config-templates/`
directory. You will find a file named `module_userregistration.php`  that you
have to copy to the main `config/` directory on your simpleSAMLphp
installation.

Most complex settings are explained below.

Mail options
------------

This module sends emails to users when they register, lose their password or get
their account modified by an administrator.

Mail options are defined under the `mail` key.

* `token.lifetime`: time in seconds for a token to expire. Tokens are generated
  to verify an email address, when requesting a password reset or when changing
  an email address. A token generated more than `token.lifetime` seconds ago
  is not considered valid.

* `from`: address that the email will be shown as sent from. You can specify a
  full `From` header, such as `Account management
  <accounts@myorganization.tld>`.

* `replyto`: will be inserted as a `Reply-To` header on all outgoing emails.

* `subject`: will be used as the `Subject` for the following outgoing emails:
  email verification after registration, password reset requests and email
  address change requests.

* `admin_create_subject` and `admin_modify_subject`: used as subjects for
  notification emails after an administrator creates or modifies an account
  respectively.

LDAP storage
------------

When using this module, users have to be stored in a LDAP server. The parameter
`storage.backend` must be set to `LdapMod`, and the `ldap` key has to contain
the following settings:

* `admin.dn`, `admin.pw`: must be set to a privileged account that can search
  your LDAP tree.
* `user.id.param`: attribute that will be used to identify an user. Must be
  defined as a known attribute inside the `attributes` option.
* `psw.encrypt`: how user passwords should be stored in LDAP.
* `user.register.email.param`: attribute containing the email address of each
  user. Must be defined as a known attribute inside the `attributes` option.
* `recover.pw.email.params`: array which has all fields that contain email
  addresses. These addresses will be used when requesting a password reset.
* Password policy: you can enforce some restrictions on your users passwords:
    * `min.length`: minimum password length
    * `require.lowercaseUppercase`: require lower case and uppercase letters
      on passwords
    * `require.digits`: require at least a digit on passwords
    * `require.any.non.alphanumerics`: require any non alphanumeric
    * `no.contains`: array of attributes of which values will not be allowed
      inside the password. So, for instance, if you specify `uid` and
      `givenName`, then the user will not be allowed to use his `uid` neither
      his `givenName` inside his password
    * `check.dicctionaries`: check inside the specified files for common words.
      Can be left as an empty array to disable this feature.
* `multivalued.attributes`: a list of attributes that can be multivalued.

There is an additional setting defined outside the `ldap` section:

* `attributes`: an associative array of attributes. The key of each entry is the
  name that simpleSAMLphp will know this attribute, and its value is the actual
  attribute name on LDAP. You can use the same name on both fields.

Search settings
---------------

The administrator user can search for users to modify/remove them. This feature
can be configured under the `search` index:

* `min_length`: Minimum length allowed to be searched
* `filter`: LDAP filter that will be used to search. `%STRING%` will be replaced
  with the search value specified. This filter will be applied to the attribute
  chosen by the administrator from a dropdown with the searchable attributes
  (see below)
* `searchable`: list of attributes that can be used to search
* `pagination`: whether search results should be paginated or not
* `elems_per_page`: how many results should be shown per page if pagination is
  enabled

Form fields
-----------

This module lets you configure how your attributes should be presented in
fields, and when they should be shown.

For each attribute you have to define the following options inside the
`formFields` setting:

* `validate`: one of the filters defined by PHP as
  [Filter constants](http://www.php.net/manual/es/filter.constants.php).
* `layout`: 
    * `control_type`: what type of field is this (valid types are `text`, `password` and
      `text`), 
    * `show`: a list of forms where this field has to be shown
    * `read_only`: a list of forms from the previous list where the field will
      be shown, but will not be editable

Note that some _virtual_ fields are defined, such as `pw1`, `pw2`, `oldpw` and
`newmail`. They are all required.


### Available forms ###

* `new_user`: user tries to register by himself
* `edit_user`: user tries to update his account details
* `admin_new_user`: admin user creation form
* `admin_edit_user`: admin account modification form
* `first_password`: user is setting his own password after registering
* `change_password`: user is changing his password
* `change_mail`: user is changing his mail address


Known email providers
---------------------

After registering an account, a verification mail is sent to the user. To make
even easier the process, you can configure some known email providers (e.g.
Gmail), so in case an address matches a known provider then the user will be
shown a direct link to his inbox.

Known email providers require the following parameters:

* `name`: provider name
* `regexp`: Regexp that the email address has to match
* `url`: URL to the mail provider web client
* `image`: provider logo


Extastorages
------------

To store the tokens and some other temporary information, userregistration needs
an additional storage besides the LDAP server. This storage is known as _extra
storage_.

The following storages are currently supported:

* Redis
* MongoDB
* PEAR::Cache_Lite

Specify which one of them you will use (`extraStorage.backend`) and configure it
on its own option (`redis`, `mongodb` or `cachelite`).
