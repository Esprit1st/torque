This repo is a fork from surfrock66 adaption of the OpenTorque Viewer, originally developed by econpy.

This repo contains everything needed to setup an interface for uploading ODB2 data logged from your car in real-time using the [Torque Pro](https://play.google.com/store/apps/details?id=org.prowl.torque) app for Android.

The interface allows the user to:

  * View a OpenStreet Map showing your trips logged via Torque
  * Create time series plots of OBD2 data
  * Easily export data to CSV or JSON

# Requirements #

These instructions assume you already have a LAMP-like server (on a Linux/UNIX based host) or have access to one. Specifically, you'll need the following:

  * MySQL database
  * Apache webserver
  * PHP server-side scripting

If in doubt, I'd recommend using Ubuntu LTS.

You also need a Bluetooth ODBII adapter, the first one was recommended by the former authors, I am using the second one:

[BAFX Products 34t5 Bluetooth OBDII Scan Tool for Android Devices](http://www.amazon.com/gp/product/B005NLQAHS)  
[Panlong Bluetooth OBD2 OBDII Car Diagnostic Scanner Check Engine Light for Android - Compatible with Torque Pro](https://www.amazon.com/gp/product/B00PJPHEBO)

You can buy ODB2 extension cables if the adapter is in a weird position. I have an extension cable with a switch, so the adapter can easily be turned off without putting too much strain on the ODB2 port.

# Server Setup #

First clone the repo:

```bash
git clone https://github.com/surfrock66/torque
cd torque
```

### Configure MySQL ###

To get started, create a database named `torque` and a user with permission to insert and read data from the database. In this tutorial, we'll create a user `steve` with password `zissou` that has access to all tables in the database `torque` from `localhost`:

```sql
CREATE DATABASE torque;
CREATE USER 'steve'@'localhost' IDENTIFIED BY 'zissou';
GRANT USAGE, FILE TO 'steve'@'localhost';
GRANT ALL PRIVILEGES ON torque.* TO 'steve'@'localhost';
FLUSH PRIVILEGES;
```

Then create a table in the database to store the logged data using the `create_torque_log_table.sql`, the `create_torque_sessions_table.sql`, and the `create_torque_keys_table.sql` files provided in the `scripts` folder of this repo: 

```bash
mysql -u yoursqlusername -p < scripts/create_torque_log_table.sql
mysql -u yoursqlusername -p < scripts/create_torque_sessions_table.sql
mysql -u yoursqlusername -p < scripts/create_torque_keys_table.sql
```

### Configure Webserver ###

Move the contents of the `web` folder to your webserver and set the appropriate permissions. For example, using an Apache server located at `/var/www`:

```bash
mv web /var/www/torque
cd /var/www/torque
find . -type d -exec chmod 755 {} +
find . -type f -exec chmod 644 {} +
```

Rename the `creds-sample.php` file to `creds.php`:

```bash
mv creds-sample.php creds.php
```

Then edit/enter your MySQL username and password in the empty **$db_user** and **$db_pass** fields:

```php
...
$db_host = 'localhost';
$db_user = '**steve**';
$db_pass = '**zissou**';
$db_name = 'torque';
$db_table = 'raw_logs';
$db_keys_table = 'torque_keys';
$db_sessions_table = 'sessions';
$gmapsApiKey = ''; // OPTIONAL Create a key at https://developers.google.com/maps/documentation/javascript/
...
```

# Settings in Torque App #

To use your database/server with Torque, open the app on your phone and navigate to:

```
Settings -> Data Logging & Upload
```
Below are the options which seem to work best for optimal logging, the suggested "File Logging" interval is 1s:

<div align="center" style="padding:15px; display:block;"><img src="http://www.surfrock66.com/images/torque_screenshot_1.png" width="45%" float="left" /><img src="http://www.surfrock66.com/images/torque_screenshot_2.png" width="45%" float="right" /></div>

Additionally, change the web logging interval to 1s or greater. Some users may want to set this to >=5s. I recommend 3s or 5s.  
A setting of 1s might be too fast depending on the amount of PIDs read and sent. I've had very consistent data with 3s and 5s.  

<div align="center" style="padding-bottom:15px; display:block;"><img src="http://www.surfrock66.com/images/torque_screenshot_3.png" width="45%" float="left" /><img src="http://www.surfrock66.com/images/torque_screenshot_4.png" width="45%" float="right" /></div>

Enter the URL to your **upload_data.php** script under "Webserver URL" and press `OK`. Test that it works by clicking `Test settings` and you should see a success message like the image on the right:

<div align="center" style="padding-bottom:15px; display:block;"><img src="http://www.surfrock66.com/images/torque_screenshot_5.png" width="45%" float="left" /><img src="http://www.surfrock66.com/images/torque_screenshot_6.png" width="45%" float="right" /></div>


At this point, you should be all setup. The next time you connect to Torque in your car, data will begin syncing into your MySQL database in real-time!

### Gotchas ###

If you log a ton of PID's (as I do for debugging) you may encounter an apache bug; Torque uploads data through a huge $_GET request.  Apache, by default, allows $_GET requests up to 8190 characters.  My sample data upload was 13,619 characters long...this led to some data uploads returning 414 errors instead of 200 responses, which resulted in the app trying over-and-over to re-upload the datapoint, essentially DDoS'ing myself.  For this; you can edit your apache configuration to allow any value you like; this is, in general, not recommended and should be set to limit a value as close as possible to what your longest query would be.  On my configuration, I edited /etc/apache2/sites-available/000-default.conf and added the following line:

```
LimitRequestLine 15000
```

### Roadmap ###

* Sanity Checks and Warnings for merges and deletes
* Allow for csv imports...captures un-uploaded but recorded data when emailed from torque
* Idea: speed heatmap for the map track. (Google Maps iOS API has gradient polylines, javascript API does not...may not be possible for now).

### Credits and Thanks ###

* [Ian Hawkins](http://ian-hawkins.com/) - Creator of the Torque app, none of this happens without that
* [Matt Nicklay/econpy](https://github.com/econpy) - This is the original project, so all credit where credit is due
* [Joe Gullizzo/surfrock66](https://github.com/surfrock66) - The project I forked from, credit where credit is due
