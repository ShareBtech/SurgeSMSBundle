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

In order to use this plugin you will need an account with [Message Whiz](https://messagewhiz.com).  It is easy to setup an account and their support is excellet.
1. Click here to open an account
2. You will receieve a verification email to verify your email address.
3. MessageWhiz works with over x countries and will open a route for the different countries you want to SMS to.


