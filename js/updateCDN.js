let fs = require('fs');
let yarnlock = require('parse-yarn-lock');

let lockfile = fs.readFileSync('yarn.lock', 'utf8');
let phpfile = fs.readFileSync('static.js_css.php.in', 'utf8');

yarnlock.parse(lockfile, function(err, parsed) {
  for(let propName in parsed) {
    let parts = propName.split('@');
    let jsName = parts[0].toLocaleUpperCase();
    let version = parsed[propName].version;
    let needle = '%'+jsName+'_VERSION%';
    while(phpfile.indexOf(needle) !== -1) {
      phpfile = phpfile.replace('%'+jsName+'_VERSION%', version);
    }
  }
  fs.writeFileSync('static.js_css.php', phpfile);
});

