Set up your server:
===================

Server Requirements:

	* Ubuntu 14.04 LTS (ssh) 
	* a new user deploy (assigned to www, sudoer)
	* LAMP Stack 
	* git installed


The following instructions are designed for a digitalocean server set up however the same could be true fo any ubunutu installation. 

Set up deploy user
---------------------
[https://www.digitalocean.com/community/tutorials/how-to-automate-php-app-deployment-process-using-capistrano-on-ubuntu-13](https://www.digitalocean.com/community/tutorials/how-to-automate-php-app-deployment-process-using-capistrano-on-ubuntu-13#preparing-the-deployment-server)

* ssh connect into your new server 
				`ssh {user}@{ipaddress}` 

* create a new group called www
				`sudo addgroup www`

* add a new user 
		`sudo adduser deploy` 

Make a note of the password your provide for this account!!!!

* assign the user to the www group 
		`sudo adduser deploy www` 

*  add the deploy user to the www group 
		`nano /etc/sudoers` 

*  append the following right after `root ALL=(ALL) ALL`

		deployer ALL=(ALL:ALL) ALL

*  Press CTRL + X and confirm with Y to save an exit 

Create the Application Deployment Directory
--------------------------------
[https://www.digitalocean.com/community/tutorials/how-to-automate-php-app-deployment-process-using-capistrano-on-ubuntu-13](https://www.digitalocean.com/community/tutorials/how-to-automate-php-app-deployment-process-using-capistrano-on-ubuntu-13#creating-the-application-deployment-directory)

	sudo mkdir /var/www

*  Please note latest versions of ubuntu with LAMP/ LEMP stacks in fact have /var/www/html/ as the default website root... you will need to update the default vhost script later 

*  Set the permissions to this folder to the www group and therefore deploy user

	`sudo chown -R :www  /var/www` 			#set ownership to www group 

	`sudo chmod -R g+rwX /var/www`			#set folder permissions recursively 

	`sudo chmod g+s /var/www`				#ensure permissions affect future directories 

Set up your LAMP environment
----------------
[https://www.digitalocean.com/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu](https://www.digitalocean.com/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu#step-one—install-apache)

*  ssh into your server and ensure apt is up to date

	`sudo apt-get update` 	
	`sudo apt-get install apache2`

*  double check your ip address: 

	`ifconfig eth0 | grep inet | awk '{ print $2 }'` 

*  Install MySQL, make a note of the root password you set in the next steps!!!! 

	`sudo apt-get install mysql-server libapache2-mod-auth-mysql php5-mysql` 

*  activate MySQL 

	`sudo mysql_install_db` 

*  Secure your installation

	`sudo /usr/bin/mysql_secure_installation`

Install PHP 

`sudo apt-get install php5 libapache2-mod-php5 php5-mcrypt`

*  Be sure to add index.php to the list of files your server recognises

`sudo nano /etc/apache2/mods-enabled/dir.conf` 

this file should look like this when you have finished: 
		
		<IfModule mod_dir.c>

		DirectoryIndex index.php index.html index.cgi index.pl index.php index.xhtml index.htm

		</IfModule>	
		

*  Create a phpinfo file in your current website root directory 

	`sudo touch /var/www/html/phpinfo.php` 

* edit your phpinfo file 

	`nano /var/www/html/phpinfo.php` 

* copy and paste the following into the blank file 

		<?php
			phpinfo();
		?>

* Press CTRL + X and confirm with Y to save an exit 

*  You will need to restart apache in order for the changes to take effect: 

	`sudo service apache2 restart` 

*  Visit your newly created page: 

http://{ipaddress}/info.php

Create SSH access between your local machine and the newly created server: 
---
[https://www.digitalocean.com/community/tutorials/how-to-set-up-ssh-keys--2](https://www.digitalocean.com/community/tutorials/how-to-set-up-ssh-keys--2)

*  Navigate to your .ssh directory
		`cd ~/.ssh/` 

*  You will need to pick an appopriate key, our deployment console requires a rsa ssh key... See what is available via: 
		`ls -la` 

*  Choose your rsa key from the list, or generate a new one via: 

	`ssh-keygen -t rsa` 

	when prompted, just hit enter:

		Enter file in which to save the key (~/.ssh/id_rsa):

	hit enter again to provide a blank passphrase: 

		Enter passphrase (empty for no passphrase):
		
		ssh-keygen -t rsa
		Generating public/private rsa key pair.
		Enter file in which to save the key (/home/demo/.ssh/id_rsa): 
		Enter passphrase (empty for no passphrase): 
		Enter same passphrase again: 
		Your identification has been saved in /home/demo/.ssh/id_rsa.
		Your public key has been saved in /home/demo/.ssh/id_rsa.pub.
		The key fingerprint is:
		4a:dd:0a:c6:35:4e:3f:ed:27:38:8c:74:44:4d:93:67 demo@a
		The key's randomart image is:
		+--[ RSA 2048]----+
		|          .oo.   |
		|         .  o.E  |
		|        + .  o   |
		|     . = = .     |
		|      = S = .    |
		|     o + = +     |
		|      . o + o .  |
		|           . o   |
		|                 |
		+-----------------+

*  Now we need to get the public part of your new SSH key up to the server, create the appropriate /home/deploy/.ssh folder and files: 

	`cat ~/.ssh/id_rsa.pub | ssh deploy@{ipaddress} "mkdir -p ~/.ssh && cat >>  ~/.ssh/authorized_keys"`

	you will be prompted to provide the password for the newly created deploy account 

*  You will want to check that you now have SSH access via the deploy account to your new server. Try the following command: 

	`ssh deploy@ENTER-YOUR-IP-ADDRESS` 

	If everything went well you should login into the server without the need for a password

Further Mods to MySQL 
----------
[https://www.digitalocean.com/community/tutorials/how-to-set-up-a-remote-database-to-optimize-site-performance-with-mysql#set-up-remote-wordpress-credentials-and-database](https://www.digitalocean.com/community/tutorials/how-to-set-up-a-remote-database-to-optimize-site-performance-with-mysql#set-up-remote-wordpress-credentials-and-database)

* Now that we have our LAMP/ LEMP stack and SSH access for our deploy user account we need to set up MySQL a little further: 

*  ssh into your server `ssh deploy@{ipaddress}` and start the MySQL CLI: 
		`mysql -u root -p` 

	you will be prompted to give the root password for the database 

*  Create a new database according to joomla-vagrant standards... all databases are prefixed with `sites_` therefore if your website was pomander.dev then your database name should be called `sites_pomander` 

		CREATE DATABASE `sites_pomander`;

*  We now want to create a deploy user that is able to control this database 

	`CREATE USER 'deploy'@'localhost' IDENTIFIED BY 'ENTER-YOUR-PASSWORD';`

* give full access to this database: 

	`GRANT ALL PRIVILEGES ON ENTER-DATABASE-NAME.* TO 'deploy'@'localhost';`

*  We should now make sure we can connect into this database via Sequel Pro.  

*  Open up Sequel Pro and create a new connection (SSH)
	We are going to use our SSH connection to establish a tunnel into the server from which we can access the mysql database locally.

*  You should provide the following configuration: (please note you will need to change the SSH host to the IP of your new server)


*  Save changes and then click, connect. If you are able to connect you have successfully connected to your server, and tunnelled your access to the MySQL database

Change the default web root
---
* As we mentioned earlier, new apache and nginx installations prefer the web root to be 

		/var/www/html
		
* We therefore need to change this to match that of our vagrant box
* Open up the default apache vhost file on the server 

		sudo nano /etc/apache2/sites-available/000-default.conf
* Change the following line 
		
		DocumentRoot /var/www/html
		
* to: 

		DocumentRoot /var/www/{sitename} 
		
sitename being the name of your website within the joomla-vagrant box. E.g `pomander.dev` would be the url of your site... `sites_pomander` would be your database. The sitename would be `pomander`

Finally we just need to install git on the server 
----
[https://www.digitalocean.com/community/tutorials/how-to-install-git-on-ubuntu-14-04#how-to-install-git-with-apt](https://www.digitalocean.com/community/tutorials/how-to-install-git-on-ubuntu-14-04#how-to-install-git-with-apt)

*  ssh into your server 
`ssh deploy@{ipaddress}` 
	
*  install git by running the following command
`sudo apt-get install git`

Your server should be ready to accept deployments from your joomla-vagrant box, you just need to set up an individual deployment projects. Please see the next section.





