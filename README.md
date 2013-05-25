BindUpd
=======

## A few words about history

BindUpd is a project I have been developing a long time ago, in a summer of 2009 using a lot of OOP in PHP5.

## Software dependencies

1. PHP >= 5.3.0
2. MySQL >= 5.1

## Installation guide

1. Create blank file `named.conf.upd` (e.g. in the same directory where `named.conf` is located) and include it in the `named.conf`:
`` include "/var/named/named.conf.upd"; ``
2. Set values for BindUpd properties in `config.inc.php`:
	1. `$mysql_host` - MySQL host address (e.g. `locahost`)
	2. `$mysql_user` - MySQL username (e.g. `root`)
	3. `$mysql_password` - MySQL password (e.g. `passwd`)
	4. `$mysql_db` - MySQL database name (e.g. `bindupd`)
	5. `$configFileName` - full path to `named.conf.upd` (e.g. `/var/named/named.conf.upd`), access for reading and writing of this file for PHP user must be provided
	6. `$path` - full path to directory where zone files are kept, with trailing slash. Should be the same directory defined in `options` clause of `named.conf` (e.g. `/etc/cache/bind/`), access for reading and writing of this file for PHP user must be provided
	7. `$reload_cms` - shell command for reloading a configuration of BIND server (e.g. `rndc reload`)
3. Create a database with a name, according to configured one, and execute `bindupd.ddl` to create initial tables schema.

## Usage

First user with name `admin` and password `password` is initially created with all the database tables and records in them.

Any user that can access `index.php` and `register.php` can register himself in the BindUpd system. Newly created account will be initially disabled. Any user with admin privileges can enable user's account using 'Users' section.

User without administrative privileges can manage all the zones, records and directives using 'Zones' section. User with administrative privileges has got access to sections 'Users' and 'Operations'.

### Initial login screen

Initial login screen lets users authenticate using their usernames and passwords, as they were mentioned during the registration.

### Zones section

Zones section can be accessed using a menu located on the top of every page when user is logged in. It has got three sub-sections: View, Edit and Add. There you can view, edit or add zones, respectively. You can switch between them without page reloading using proper links below the menu.

While viewing, you can also delete zone or view zone's records or directives using proper links in the zones listing table. Records and Directives sections look the same as Zones section and can be used the same way.

Using Edit sub-section you can edit unlimited number of zones (or records, or directives), the only condition is to place a checkbox in every line where you make changes and want these changes to be saved in BIND configuration files.

### Restart section

Restart section allows any user to restart BIND using the command mentioned in `config.inc.php` file.

### Users section

Users section can be accessed only by administrators. There you can disable or enable users, and also you can set users' types – general user or admin.

### Operations section

Operations section can be accessed only by administrators. There you can inspect all previous changes made in BIND configuration files using BindUpd web interface. Also you can apply filters: for a particular user and/or particular kind of operation, and sort data by any available field.
