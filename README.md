# Burning Flipside Common Code
[![Build Status](https://travis-ci.org/BurningFlipside/CommonCode.svg?branch=master)](https://travis-ci.org/BurningFlipside/CommonCode)
[![Coverage Status](https://coveralls.io/repos/github/BurningFlipside/CommonCode/badge.svg?branch=master)](https://coveralls.io/github/BurningFlipside/CommonCode?branch=master)
[![Code Climate](https://codeclimate.com/github/BurningFlipside/CommonCode/badges/gpa.svg)](https://codeclimate.com/github/BurningFlipside/CommonCode)

# Burning Flipside Dev Enviornment Setup

To setup a dev environment for the Burning Flipside Web Environment please do the following:

1. Goto /var/www
2. Run sudo mkdir common
3. Run sudo chown \`whoami\`:\`whoami\` common
4. Run git clone git@gitlab.com:BurningFlipside/CommonCode.git common
5. Goto /var/www/html
6. Run sudo mkdir profiles
7. Run sudo chown \`whoami\`:\`whoami\` profiles
8. Run git clone git@gitlab.com:BurningFlipside/ProfilesSystem.git profiles
9. Run sudo mkdir secure
10. Run sudo chown \`whoami\`:\`whoami\` secure
11. Run git clone git@gitlab.com:BurningFlipside/SecureFramework.git secure
12. Goto /var/www/common, /var/www/html/profiles, /var/www/html/secure and switch to the current development branch (The current development branch is **version2**). The command for this is git checkout origin/version2
13. Run git submodule update --init in each of the three directories as well
14. Goto /var/www
15. Run sudo mkdir secure_settings
16. Run sudo chown \`whoami\`:\`whoami\` secure_settings
17. Use the following as class.FlipsideSettings.php in the secure_settings folder:

```
    <?php
    class FlipsideSettings
    {
        public static $global = array(
            'use_minified' => false,
            'use_cdn'      => false,
            'login_url'    => '/profiles/login.php'
        );
        public static $authProviders = array(
            'Auth\\OAuth2\\FlipsideAuthenticator' => array(
                'current' => true,
                'pending' => true,
                'supplement' => false
            )
        );
        public static $sites = array(
            'Profiles'=>'https://profiles.burningflipside.com',
            'WWW'=>'http://www.burningflipside.com',
            'Pyropedia'=>'http://wiki.burningflipside.com',
            'Secure'=>'https://secure.burningflipside.com'
        );
    }
    /* vim: set tabstop=4 shiftwidth=4 expandtab: */
    ?>
```

18. Run sudo /var/www/common/cron.sh
