# TranslateApi
RESTful API to manage translations using silex and redis

## Is it reliable ?

No, this repo is for test purposes only.
Main goal is to play around with both silex and Redis.

## How to ?

First download and install vagrant see http://docs.vagrantup.com/v2/installation/index.html for more info.
To download the full puphpet configuation go to http://puphpet.com and drag puphpet/config.yaml in your browser to upload.
Extract and copy all files/folders to you project folder.

When vagrant is install and puphpet config is added go to the project folder, run the following command and grab a coffee
	
    vagrant up

The vagrant file is automatically installing all the tools you need. 
After the virtual machine is up and running.
    
    vagrant ssh
    
Before the app is ready to go, run composer. Composer is pre-installed on the VM
    
    cd /var/www/translation
    composer install
    
Once you're logged on to the server you can access redis with the following command.
   
    redis-cli
    
## How to use ?    
There are 5 different API calls

Get a translation

    /{language}/{key}
    /nl_NL/hello
    
Add a translation

    /{language}/{key}/{value}
    /nl_NL/hello/hallo

Delete all translation for given key

    /{key}
    /hello
    
List translations

    /list/{lang}
    /list/nl_NL
    
Count number of translations

    /count/{lang}
    /count/nl_NL
    
    
By default nl_NL and en_GB are accepted languages. To add more edit index.php on line 10.     

    
## Tips
* Edit your hostfile to call the api via url. translation.api 192.168.88.100.
* Edit puphpet/config.yaml to for example change the url or local ip.

## Contributing ?
Feel free to use, fork or add issues.

## Thanks
Thanks to https://puphpet.com/ for creating a simple web interface to configure vagrant boxes



