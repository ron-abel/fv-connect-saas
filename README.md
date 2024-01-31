# Filevine Connect SAAS
SaaS consists of 3 main apps
- Super Admin Portal
- Tenant Client Portal
- Tenant Admin portal

## Sample Urls for portals
- Super Admin Portal : https://vineconnect.vinetegrate.com
- Tenant Clint Portal: http://first.vinetegrate.com/
- Tenant Admin Portal: http://first.vinetegrate.com/admin

"first" means the tenant name.
DB
UN: 
PW: 

# How to Install

## First, clone the repo on Wamp/Xampp
```
git clone https://github.com/tomgoldlaw/Vineconnect-SaaS.git
```

## Second, set up the local DB with the latest backup
The latest DB, filevine_connect_saas_db.sql exists under tests/DB directory of the repo
- create the db name "fv_saas" on mysql.
- should migrate the latest db format.
- Install sql file to the created DB on localhost.

## Third, configure the Vhost on Wamp/Xampp and hosts on the system

set up virtual host configuration for the super admin and tenant portal on localhost. 

Reference this article, https://www.osradar.com/how-to-install-apache-virtual-host-in-windows-10/

localhost apache web server > httpd-vhosts.conf

Super admin: vineconnect.fv_saas.com

one of the tenants : first.fv_saas.com

If you'd like to test another tenant, must set the tenant on vhost configuration file.  like this second.fv_saas.com

example: 

    <VirtualHost *:80>
        ServerName vineconnect.fv_saas.com
        ServerAlias vineconnect.fv_saas.com
        DocumentRoot "${INSTALL_DIR}/www/Vineconnect-saas/public"
        <Directory "${INSTALL_DIR}/www/Vineconnect-saas/public">
            Options +Indexes +Includes +FollowSymLinks +MultiViews
            AllowOverride All
            Require local
        </Directory>
    </VirtualHost>

    <VirtualHost *:80>
        ServerName first.fv_saas.com
        ServerAlias first.fv_saas.com
        DocumentRoot "${INSTALL_DIR}/www/Vineconnect-saas/public"
        <Directory "${INSTALL_DIR}/www/Vineconnect-saas/public">
            Options +Indexes +Includes +FollowSymLinks +MultiViews
            AllowOverride All
            Require local
        </Directory>
    </VirtualHost>
    ```

## Fourth, Set up laravel project on localhost
- copy ".env.example" and past with rename. ".env"
    On .env file, need to set up Twilio credential info, so, it's possible to make it works on localhost.
- "composer install" to install laravel packages. 
- visit the site superadmin portal :  vineconnect.fv_saas.com
- visit the site tenant portal : first.fv_saas.com

# How to Use the app

## First, log in the super admin portal with the below credential
```
Email: filevinecvsuperadmin@fvconnect.com
Password: "Abc123"
```

## Second, create the tenant in vineconnect.vinetegrate.com/admin/tenants

Once you create the tenant on the superadmin, the tenant app is available with <tenant_name>.vinetegrate.com.

The Credential of this tenant admin is <tenant_name>.admin@vinetegrate.com / <tenant_name>password

## Third, the tenant user can log in the tenant admin and configure the needed settings
- Once the tenant admin logs in its admin site, <tenat_name>.vinetegrate.com/admin with the above credential, he can update his name, email, and password.
- Next, he needs to configure the settings on Setting / Credential page, <tenat_name>.vinetegrate.com/admin/settings
- Once the settings are configured, the client portal <tenat_name>.vinetegrate.com is available.

## FOR MAC USING MAMP PRO
Each time the database updates, we must recompile.
In terminal, navigate to the root folder of the saas using the "cd" command.
Install composer packages with command "composer install"
Run the last command "php artisan migrate --force"
Restart the MAMP server and load host.


## Connect to MySQL in hosting server, using Terminal
- Connect Hosting server using Putty. 
    To connect to the SSH, need to know IP of the hosting server and username, PPK file key.
- Once connect to the SSH, need to connect DB using MYSQL command. 
    https://www.hostmysite.com/support/linux/mysql/access/ 
    
