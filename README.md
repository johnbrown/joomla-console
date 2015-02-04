Joomla Command Line Tools
=========================

This is a script developed by [Joomlatools](http://joomlatools.com) to ease the management of Joomla sites.

It is designed to work on Linux and MacOS. Windows users can use it in [Joomlatools Vagrant box](https://github.com/joomlatools/joomla-vagrant)

Installation
------------

1. Download or clone this repository.

1. Make the `joomla` command executable:

    `$ chmod u+x /path/to/joomla-console/bin/joomla`

1. Configure your system to recognize where the executable resides. There are 3 options:
    1. Create a symbolic link in a directory that is already in your PATH, e.g.:

        `$ ln -s /path/to/joomla-console/bin/joomla /usr/bin/joomla`

    1. Explicitly add the executable to the PATH variable which is defined in the the shell configuration file called .profile, .bash_profile, .bash_aliases, or .bashrc that is located in your home folder, i.e.:

        `export PATH="$PATH:/path/to/joomla-console/bin:/usr/local/bin"`

    1. Add an alias for the executable by adding this to you shell configuration file (see list in previous option):

        `$ alias joomla=/path/to/joomla-console/bin/joomla`

    For options 2 and 3 above, you should log out and then back in to apply your changes to your current session.

1. Test that joomla executable is found by your system:

    `$ which joomla`

1. From joomla-console root (/path/to/joomla-console), run Composer to fetch dependencies.

    `$ composer install`

For available options, try running:

    joomla --list
    
Usage 
-----

### Create Sites

To create a site with the latest Joomla version, run:

    joomla site:create testsite

The newly installed site will be available at /var/www/testsite and testsite.dev after that. You can login into your fresh Joomla installation using these credentials: `admin` / `admin`.

By default the web server root is set to _/var/www_. You can pass _--www=/my/server/path_ to commands for custom values.

You can choose the Joomla version or the sample data to be installed:

    joomla site:create testsite --joomla=2.5 --sample-data=blog

You can pick any branch from the Git repository (e.g. master, staging) or any version from 2.5.0 and up using this command.

You can also add your projects into the new site by symlinking. See the Symlinking section below for detailed information.

    joomla site:create testsite --symlink=project1,project2

For more information and available options, try running:

    joomla site:create --help

### Delete Sites

You can delete the sites you have created by running:

    joomla site:delete testsite

### Symlink Extensions

Let's say you are working on your own Joomla component called _Awesome_ and want to develop it with the latest Joomla version.

By default your code is assumed to be in _~/Projects_. You can pass _--projects-dir=/my/code/is/here_ to commands for custom values.

Please note that your source code should resemble the Joomla folder structure for symlinking to work well. For example your administrator section should reside in ~/Projects/awesome/administrator/components/com_awesome.

Now to create a new site, execute the site:create command and add a symlink option:

	  joomla site:create testsite --symlink=awesome

Or to symlink your code into an existing site:

    joomla extension:symlink testsite awesome

This will symlink all the folders from the _awesome_ folder into _testsite.dev_.

Run discover install to make your component available to Joomla and you are good to go!

For more information on the symlinker, run:

	  joomla extension:symlink  --help

### Install Extensions

You can use discover install on command line to install extensions.

    joomla extension:install testsite com_awesome

You need to use the _element_ name in your extension manifest.

For more information, run:

	  joomla extension:install --help
	  
Alternatively, you can install extensions using their installation packages using the `extension:installfile` command. Example:

    joomla extension:installfile testsite /home/vagrant/com_component.v1.x.zip /home/vagrant/plg_plugin.v2.x.tar.gz
    
This will install both the com_component.v1.x.zip and plg_plugin.v2.x.tar.gz packages.

### Extra commands

There a few other commands available for you to try out as well :

* `joomla site:token sitename user` : generates an authentication token for the given `user` to automatically login to `sitename` using the ?auth_token query argument. *Note* requires the [Koowa framework](https://github.com/joomlatools/koowa) to be installed in your `site`.
* `joomla versions` : list the available Joomla versions. 
 * Use `joomla versions --refresh` to get the latest tags and branches from the official [Joomla CMS](https://github.com/joomla/joomla-cms) repository.
 * To purge the cache of all Joomla packages, add the `--clear-cache` flag to this command.

## Requirements

* Composer
* Joomla version 2.5 and up.

## Contributing

Fork the project, create a feature branch, and send us a pull request.

## Authors

See the list of [contributors](https://github.com/joomlatools/joomla-console/contributors).

## License

The `joomlatools/joomla-console` plugin is licensed under the MPL v2 license - see the LICENSE file for details.

Deployment
-------
You are now able to deploy your entire site to any number of servers, this avoids the need to use insecure methods of file transfer such as FTP and will even create your database for you. 

You live/ development/ staging server needs to be able to authenticate with your vagrant box before deployments can take place. Follow these instructions to set up your deployment service. 

## Initiate deployment locally 
To initiate your deployment service on a project basis by simply issuing this command: 

`joomla deploy:setup awesome`

This will create a /deploy/ folder under the awesome site which could be found at the following location: 

	/var/www/awesome/deploy/ 

In the deploy folder you will find all you need to connect to your web server. You will notice a development.yml file... this will contain all the configuration details for your development web server. Create a new environment for each web server you will connect to.  

If you wish to connect to your web server via SSH (highly recommended) the deploy ssh command is able to generate these keys and enable this authentication for you.

##Create deployment ssh connection
Your local vagrant box and project needs to be able to authenticate against your server(s), this can either happen with a username and password (less secure), or we can generate a SSH connection between the vagrant box and the server(s). 

To set up the ssh connection simple run the following command: 

`joomla deploy:ssh deploy 123.45.67.89` 

The additional options provided above represent user (deploy) and the ip address of your server (123.45.67.89) simply change these details for your server.  You will be prompted for the user's password, but after this your project will be able to authenticate via SSH. 

In addition to creating the SSH authentication, the ssh command also updates your environment yml file with the user, app and database ip address.

##Edit your environment configuration
You can of course edit your environment configuration files by hand, they are yml based. You can find out more about this syntax here:
[http://docs.ansible.com/YAMLSyntax.html](http://docs.ansible.com/YAMLSyntax.html)

But we've also created a console command that enables you to change any configuration item: 

`joomla deploy:edit --user deploy --app 123.45.67.89 --branch develop --backup true`

This is the complete list of configurable items:

		user: deploy
		repository: 'https://github.com/****/****.git'
		deploy_to: /var/www/awesome
		backup: null
		branch: develop
		remote_cache: true
		scm: git
		releases: true
		revision: true
		key_path: /var/www/awesome/deploy/id_rsa
		key_path_deployed: true
		password: null
		app: 178.62.33.225
		db: 178.62.33.225
		database:
    		name: sites_pomander
    		user: deploy
    		password: testing
    		host: 127.0.0.1
    		charset: utf8
    		
To see the configuration options on the command line `joomla deploy:edit --help` 

##Create new environment configurations
You may need to deploy to many different web servers, depending on your project requirements. For instance you may have a live server, a staging server, a development server... You are able to deploy to all of these!

You simply need to create a new environment per server: 

`joomla deploy:environment staging`

This will create the new configuration file at the following location 

		/var/www/awesome/deploy/staging.yml` 
		
and will be of course used to deploy your changes to the staging server. 

##Setup deployment
Ok so you have set up your deployment for a project, you have made it possible to connect to the remote servers via ssh, configured how your project communicates with these servers, so you are ready to push your first website: 

`joomla deploy:setup awesome` 

This wil take all your site files and database and push them up to the server. Your database will be wiped and replaced with a copy of your local database so you are warned about this. Type YES to continue
