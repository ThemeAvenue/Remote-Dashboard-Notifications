Remote Dashboard Notifications
==============================

Developers, have you ever wanted to ask something to your users? Tried to get some feedback about your product? Want them to leave a comment on your site? This plugin will help you do that. 

Remote Dashboard Notifications (RDN) is a plugin made for themes and plugins developers who want to send short notifications to their users. This plugin will allow a theme / plugin author to display a notice in the client's admin dashboard using the WordPress admin notices.

<img src="http://i.imgur.com/lfj15SY.png" width="650" />

The plugin works on a server / client basis. The product author uses a WordPress install as the server (where the plugin has to be installed), and the user's WordPress site will be the client.

## How it works ##

The plugin is meant to manage messages for multiple products. We call _channels_ the different types of notifications. For instance, if I have 2 products, Product A and Product B, I will create 2 channels in the server: Channel A and Channel B.

Once a channel is created, the plugin will create an ID and a key used to authenticate the client requests. When integrating RDN to a theme or plugin, the client class will be instanciated with the desired channel ID and key.

When a client site will will check for new notifications, it will make an HTTP request (using WordPress HTTP API) to the server. If the requested channel exists and the key is valid, the server will return the latest notification (if any) in a JSON encoded array.

The client site will then cache this response and display it as an admin notice in the WordPress site dashboard until the user dismisses it.

## Integration in a theme or plugin ##

It is really easy to integrate this feature in a plugin or theme. Only two steps are required:

1. Copy `includes/class-remote-notification-client.php` into the theme / plugin directory
2. Instanciate the class with the server's URL, a channel ID and key

### Integration examples ###

#### Theme ####

Place this into `functions.php`.

    require( 'class-remote-notification-client.php' );
    $notification = new TAV_Remote_Notification_Client( 35, 'f76714a0a97d1186', 'http://server.url/?post_type=notification' );

## Privacy ##

The plugin does not collect any information about the client site. The server plugin is completely passive and its only job is to return messages to the requestor.