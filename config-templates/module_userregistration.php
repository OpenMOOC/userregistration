<?php
/**
 * The configuration of userregistration module
 */

$config = array (

    /* The authentication source that should be used. */
    'auth' => 'userregistration-ldap',

    /* The authentication source for admin views. */
    'admin.auth' => 'admin',

    // Realm for eduPersonPrincipalName
    'user.realm' => 'example.org',

    // Usen in mail and on pages
    'system.name' => 'User registration module',

    // Mailtoken valid for 5 days
    'mailtoken.lifetime' => (3600*24*5),
    'mail.from'     => 'Example <na@example.org>',
    'mail.replyto'  => 'Example <na@example.org>',
    'mail.subject'  => 'Example - email verification',

    // URL of the Terms of Service
    'tos' => '',

    // To enable/disable navigation links in the module block
    'custom.navigation' => TRUE,

    // User storage backend selector
    'storage.backend' => 'LdapMod',

    // LDAP backend configuration
    // This is configured in authsources.php
    // FIXME: The name of this arrays shoud be the same as storage.backend value
    'ldap' => array(
        'admin.dn' => 'cn=admin,dc=example,dc=org',
        'admin.pw' => 'xyz',

        // Storage User Id indicate which of the attributes
        // that is the key in the storage
        // This relates to the attributs mapping
        'user.id.param' => 'uid',
        //'user.id.param' => 'cn',

        // Password encryption
        // plain, md5, sha1
        'psw.encrypt' => 'sha1',

        // Field user to save the registration email of the user
        'user.register.email.param' => 'mail',

        // Fields that contain a valid email to recover the password
        // (Sometimes is needed to be able to send recover password mail to a different email than the register email,
        //  For example if the Mail-System of the registered mail is protected by the IdP)
        'recover.pw.email.params' => array('mail','irisMailMainAddress'),

        // Password policy
        'password.policy' => array(
            'min.length' => 7,
            'require.lowercaseUppercase' => true,
            'require.digits' => true,
            // Require that password contains a non alphanumeric letter.
            'require.any.non.alphanumerics' => true,
            // Check if password contains the user values of the params of the array. Empty array to don't check
            'no.contains' => array('uid','givenName', 'sn'),
            // Dictionay filenames inside hooks folder. Empty array to don't check
            'check.dicctionaries' => array('dict1.txt'),
        ),

        // LDAP objectClass'es
        'objectClass' => array(
            'inetOrgPerson',
            'organizationalPerson',
            'person',
            'top',
            'eduPerson',
            'norEduPerson'
        ),
    ), // end Ldap config

    // AWS SimpleDB configuration

    // SQL backend configuration

    // Password policy enforcer
    // Inspiration and backgroud
    // http://www.hq.nasa.gov/office/ospp/securityguide/V1comput/Password.htm



    /*
     * Mapping from the Storage backend field names to web frontend field names
     *
     * Valid values for show, read_only and optional settings
     *
     * 'new_user': user tries to register by himself
     * 'edit_user': user tries to update his account details
     * 'admin_new_user': admin user creation form
     * 'admin_edit_user': admin account modification form
     * 'first_password': user is setting his own password after registering
     * 'change_password': user is changing his password
     */

    'attributes'  => array(
        'uid' => 'uid',
        'givenName' => 'givenName',
        'sn' => 'sn',
        // Will be a combination for givenName and sn.
        'cn' => 'cn',
        'mail' => 'mail',
        // uid and appended realm
        'eduPersonPrincipalName' => 'eduPersonPrincipalName',
        // Set from password walidataion and encryption
        'userPassword' => 'userPassword',
    ),

    // Configuration for the field in the web frontend
    // This controlls the order of the fields
    'formFields' => array(
        // UID
        'uid' => array(
            'validate' => array(
                'filter'  => FILTER_VALIDATE_REGEXP,
                'options' => array("regexp"=>"/^[a-z]{1}[a-z0-9\-]{2,15}$/")
            ),
            'layout' => array(
                'control_type' => 'text',
                'show' => array(
                    'new_user',
                    'edit_user',
                    'admin_new_user',
                ),
                'read_only' => array(
                    'edit_user',
                ),
            ),
        ), // end uid

        'givenName' => array(
            'validate' => FILTER_DEFAULT,
            'layout' => array(
                'control_type' => 'text',
                'show' => array(
                    'new_user',
                    'edit_user',
                    'admin_new_user',
                    'admin_edit_user',
                ),
                'read_only' => array(
                ),
            ),
        ), // end givenName

        // Surname (ldap: sn)
        'sn' => array(
            'validate' => FILTER_DEFAULT,
            'layout' => array(
                'control_type' => 'text',
                'show' => array(
                    'new_user',
                    'edit_user',
                    'admin_new_user',
                    'admin_edit_user',
                ),
                'read_only' => array(
                    ),
                ),
            ), // end ename

        'mail' => array(
            'validate' => FILTER_VALIDATE_EMAIL,
            'layout' => array(
                'control_type' => 'text',
                'show' => array(
                    'new_user',
                    'edit_user',
                    'admin_new_user',
                    'admin_edit_user',
                ),
                'read_only' => array(
                    'edit_user',
                ),
            ),
        ), // end mail

        // Common name: read only
        'cn' => array(
            'validate' => FILTER_DEFAULT,
            'layout' => array(
                'control_type' => 'text',
                'size' => '35',
                'show' => array(
                    'new_user',
                    'edit_user',
                    'admin_new_user',
                    'admin_edit_user',
                ),
                'read_only' => array(
                ),
            ),
        ), // end cn

        // eduPersonPrincipalName
        'eduPersonPrincipalName' => array(
            'validate' => FILTER_DEFAULT,
            'layout' => array(
                'control_type' => 'text',
                'show' => array(
                    'admin_new_user',
                    'admin_edit_user',
                ),
                'read_only' => array(
                ),
            ),
        ), // end eduPersonPrincipalName

        'userPassword' => array(
            'validate' => FILTER_DEFAULT,
            'layout' => array(
                'control_type' => 'password',
                'show' => array(
                    'first_password',
                    'change_password',
                    'admin_new_user',
                    'admin_edit_user',
                ),
                'read_only' => array(
                ),
                'optional' => array(
                    'admin_edit_user',
                ),
            ),
        ),

        'pw1' => array(
            'validate' => FILTER_DEFAULT,
            'layout' => array(
                'control_type' => 'password',
                'show' => array(
                    'change_password',
                    'admin_new_user',
                    'admin_edit_user',
                ),
                'read_only' => array(
                ),
                'optional' => array(
                    'admin_edit_user',
                ),
            ),
        ),

        'pw2' => array(
            'validate' => FILTER_DEFAULT,
            'layout' => array(
                'control_type' => 'password',
                'show' => array(
                    'change_password',
                    'admin_new_user',
                    'admin_edit_user',
                ),
                'read_only' => array(
                ),
                'optional' => array(
                    'admin_edit_user',
                ),
            ),
        ),
        'oldpw' => array(
            'validate' => FILTER_DEFAULT,
            'layout' => array(
                'control_type' => 'password',
                'show' => array(
                    'change_password',
                ),
                'read_only' => array(
                ),
            ),
        ),
    ),
);
