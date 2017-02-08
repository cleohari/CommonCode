# Burning Flipside Common Code
[![Build Status](https://travis-ci.org/BurningFlipside/CommonCode.svg?branch=master)](https://travis-ci.org/BurningFlipside/CommonCode)
[![Coverage Status](https://coveralls.io/repos/github/BurningFlipside/CommonCode/badge.svg?branch=master)](https://coveralls.io/github/BurningFlipside/CommonCode?branch=master)
[![Code Climate](https://codeclimate.com/github/BurningFlipside/CommonCode/badges/gpa.svg)](https://codeclimate.com/github/BurningFlipside/CommonCode)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/BurningFlipside/CommonCode/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/BurningFlipside/CommonCode/?branch=master)
[![Coverity Scan Build Status](https://scan.coverity.com/projects/9928/badge.svg)](https://scan.coverity.com/projects/flipside-common-code)
[![Apache 2 licensed](https://img.shields.io/badge/license-apache-blue.svg)](https://github.com/BurningFlipside/CommonCode/blob/master/LICENSE)

# Burning Flipside Dev Enviornment Setup

To setup a dev environment for the Burning Flipside Web Environment please do the following:

1. Goto /var/www
2. Run sudo mkdir common
3. Run sudo chown \`whoami\`:\`whoami\` common
4. Run git clone https://github.com/BurningFlipside/CommonCode.git common
5. Goto /var/www/html
6. Run sudo mkdir profiles
7. Run sudo chown \`whoami\`:\`whoami\` profiles
8. Run git clone https://github.com/BurningFlipside/Profiles.git profiles
9. Run sudo mkdir secure
10. Run sudo chown \`whoami\`:\`whoami\` secure
11. Run git clone https://github.com/BurningFlipside/SecureFramework.git secure
12. Goto /var/www/common, /var/www/html/profiles, /var/www/html/secure and run git submodule update --init in each of the three directories as well
13. Goto /var/www
14. Run sudo mkdir secure_settings
15. Run sudo chown \`whoami\`:\`whoami\` secure_settings
16. Use the following as class.FlipsideSettings.php in the secure_settings folder:

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

17. Run sudo /var/www/common/cron.sh
