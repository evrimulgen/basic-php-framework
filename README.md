# Basic PHP Framework

This reporitory is meant to be a base project structure for PHP web apps. This framework is so barebones that I haven't even given it a real name.

## Install

#### Web Server

When running this web app, make sure that you've blocked the server from serving the `.git`, `.gitignore`, `.gitmodules`, `README.md`, `.htaccess`, and `private` files and folders somewhere in your website's configuration.

You also must redirect all requests to the root index.php file if the requested folder or file does not exist on the server.

If you're running this on *Apache 2*, the easiest way to do both of those things is to enable use of `.htaccess` files (instructions [here](https://help.ubuntu.com/community/EnablingUseOfApacheHtaccessFiles)) and enable `mod_rewrite` (instructions [here](https://stackoverflow.com/questions/869092/how-to-enable-mod-rewrite-for-apache-2-2)). Alternatively, if you don't want to use `.htaccess` files, you can simply copy the contents of that file to your website's configuration.

#### MySQL

You will also need to set up a MySQL server and store the credentials for it in your credentials.json file. This is a pretty basic task so I don't really feel I need to include instructions for how to set up MySQL. If you don't know how to do that then you probably shouldn't be using this project. Once you've done that, run the `database.sql` file in your **setup** folder to create the tables for your database.

#### PHP

You will need to install PHP along with the MySQL plugin.


## Usage

### index.php

`index.php` should be the main driver of your web application. In a normal situation, it should require your `private/main.php` file to load the code for the website, define routes for pages on the site, and then perform the routing for the site. The default `index.php` file included with this project should give an example of how to do this.

### private

The private folder is where the main code for your website should be kept. Each of its files and subfolders serves a different purpose:

* **api** - If your site has an API, you can use this folder to maintain a versioned API system.
	* Each versioned subfolder should be in the format of `vMAJORVERSION.MINORVERSION`, with the minor version being optional. Examples: `v1`, `v2.3`, etc.
	* Inside each of these versioned subfolders you can have PHP scripts or folders with PHP scripts. If a request is made to your site with the path `/api/v1.4/user/login`, it will be resolved to `private/api/v1.4/user/login.php`

* **classes** - You should put any class files that you create into this folder. They will be automatically required when the site loads (which is on every request, because PHP is weird). The *User* class is included by default and has code for signing up users and managing user sessions.

* **credentials.json** - This is where you should put any API keys, MySQL login credentials, or any other credentials you may need to access external tools. All properties of the root JSON object should be different environment configurations. By default, *production* and *development* configurations are included with empty MySQL credentials. The JSON object for your current configuration is stored in a global variable called `$credentials`. You can modify the configuration by setting the `$environment` variable in your `env.php` file

* **env.php** - This file is not created by default. If your server configuration allows PHP to create files, then it should copy the `env.php.example` file to `env.php` if it doesn't exist. Use this file to set the environment configuration for your server, by following the example in `env.php.example`.

* **external** - Any external libraries you are using should be kept in this folder. Nothing is automatically required from here currently, so you'll have to require the files yourself. Example: `require_once(PRIVATE_DIR.'/external/PHPMailer/src/PHPMailer.php');`

* **functions** - You should put PHP files containing functions into this folder. Function files are automatically required when the site loads.

* **main.php** - This file handles loading your website code, and should be required by your `index.php` file in the root of your project.

* **pages** - Page templates should go here. You can display these pages by just calling the `display_page` function and passing in a string with the name of your page. For example, calling `display_page("home")` would display the `home.php` file in your pages folder if it exists.

* **setup** - Any files that someone might need for initial setup of your project should go here. By default, `database.sql` is included, which has the code for setting up the initial database tables.

* **template-parts** - Any section of a page that might be included in multiple pages should be put in here for re-use. You can display a template part in a page by just calling the `display_template_part` and passing in a string with the name of the template part. For example, calling `display_template_part("searchbar")` would display the `searchbar.php` file in your template-parts folder if it exists.

## API Reference

So I haven't made any documentation for the functions and classes that come with the project, but most of them should be self explanatory. If anyone other than myself is actually interested in using this project I can make one, but right now that's a lot of work that I don't really want to do.
