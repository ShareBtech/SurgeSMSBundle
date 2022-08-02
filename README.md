# SurgeSMSBundle

## Description
[Surge.Media](https://surge.media) has developed a new plugin for sending out SMS using MessageWhiz's gateway.

## Purpose
The plugin is used as an alternative to other plugins that ship directly with Mautic and offers a seemless integration and setup to get you sending SMS's immediately.

## Compatability

This first version of the plugin has been tested with Mautic 3.x and Mautic 4.x

## Features

- Send Test SMS from within an SMS setup
- Send Segment SMS to a chosen segment
- Send SMS via Campaigns
- Live character count while writing out SMS

![image](https://user-images.githubusercontent.com/59197832/180973540-583f022a-67a3-4cb2-8034-acb6efa27f5e.png)

## Installation

1. Download the plugin from this github or clone it
2. Move the plugin to your plugin directory /var/www/mautic/plugins/
3. Unzip the plugin.  It will create a new directory SurgeBundle within this dorectory
4. Change the ownership to the www owner. (chown -R www-data:www-data *)
5. Clear the cache either by using mautic:cache:clear or cd /var/www/mautic/var/cache and rm -rf *
6. Run the following command from your root directory of Mautic: php bin/console mautic:assets:generate
7. Go to your web browser, Mautic to Configuration and Plugins and refresh and then click install

## Creating a MessageWhiz Account

In order to use this plugin you will need an account with [Message Whiz](https://sms.mmdsmart.com/signup?source_id=surge).  It is easy to setup an account and their support is excellet.
1. Click [here](https://sms.mmdsmart.com/signup?source_id=surge) to open an account
2. You will receieve a verification email to verify your email address.
3. MessageWhiz works with over x countries and will open a route for the different countries you want to SMS to.
4. You can use Surge.SMS Country Form to let MessageWhiz know which GEO's you would like to SMS out to.  This needs to be done in order for them to open up specific Routes for you.

## Configuring Surge.SMS Gateway

In order to Configure the Surge.SMS Gateway you will need to obtain your API key from Message Whiz.  This can be done by login into the Dashboard and going to "My Account". You will see your API key there, copy it and paste it into the API area of the plugin.

Surge has created an API connection in order to help extract your Campaign ID and Sender ID.  You can use this little application by clicking [here](https://surge.media/messagewhiz/).  You will then receive your Campaign ID and Sender ID.

![image](https://user-images.githubusercontent.com/59197832/180984317-d1bfbf43-0478-4a4a-8c36-ab5d9d383c59.png)

Next you need to input your default Sender ID and Default Campaign ID.

The sender ID is something that you can define inside Message Whiz

![image](https://user-images.githubusercontent.com/59197832/180984902-ee2e02e3-e2e3-4980-9eca-9c9a265d03cf.png)



