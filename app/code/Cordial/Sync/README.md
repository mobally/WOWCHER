# Cordial Sync For Magento 2 #

Cordial/Magento Integration RFP


## Installation

To install the module you need to clone repository into your magento 2 application folder "your-app-folder-name/app/code/Cordial/Sync/".
For example (using https):
```sh
$ cd clone <your-app-folder-name>/app/code/
$ git clone https://git.assembla.com/cordial-integration-magento.2.git Cordial/Sync/
```
or just simply download it from site and unpack in the same way.



Enable the extension and clear static view files:
```
bin/magento module:enable Cordial_Sync --clear-static-content
```
Register the extension:
```
bin/magento setup:upgrade
```
Recompile your Magento project:
```
bin/magento setup:di:compile
```
Verify that the extension is enabled:
```
bin/magento module:status
```

More details here http://devdocs.magento.com/guides/v2.2/comp-mgr/install-extensions.html


### Composer dependencies
Go on the root folder of Magento 2 (cd your-app-folder-name)
```sh

composer config repositories.cordial2 vcs https://git.assembla.com/cordial-integration-magento.2.git
composer require "cordial/sync":1.*
```
OR

Go on the root folder of Magento 2 (cd your-app-folder-name), open "composer.json" file and 
add a repository to existing ones inside the "repositories" block:

```
"repositories": {
	"..." : "...",
        "cordial2": {
            "type": "vcs",
            "url": "https://git.assembla.com/cordial-integration-magento.2.git"
        }
}
...
```
add a dependency to existing ones inside the "require" block:
```
...
"require": {
        "..." : "...",
        "cordial/sync": "1.*"
}
...

```
if you are deploying your project, just type:
```sh
$ composer install
```
if you are adding only the module into your project, then type:
```sh
$ composer update
```
Composer will install dependencies automatically.

### Upgrade magento modules
The next step is to upgrade your application in order to register the module in magento 2 system. But before that you need to check this for sure:
```sh
$ ./bin/magento setup:db:status
```
So, if there is a need you have to go on the root folder of Magento 2 (cd your-app-folder-name) and type:
```sh
$ ./bin/magento setup:upgrade
```


Do not forget about permissions for module:
```sh
sudo find . -type d -exec chmod 755 {} \; && sudo find . -type f -exec chmod 644 {} \;
```

