# JS Addons
 <img src="https://raw.githubusercontent.com/ticgal/jsaddons/multimedia/jsaddons-logo.png" alt="JS Addons Logo" height="250px" width="250px" class="js-lazy-loaded">
 JS Addons is developed to allow the use of several useful web tools in GLPI by inserting JavaScript snippets for them to execute.  

Currently, **JS Addons** supports:

Analytics:
  - [Metricool](http://mtr.cool/yfuhbk)
  - Google Analytics

Chat:
  - [Tawk.to](https://www.tawk.to/?pid=snaotzu)

## How to use

### Install

Install the plugin as usual or using the new GLPI 9.5 Marketplace.

### Permissions

The user profile must be able to edit GLPI configuration. This user will usually need a Super-Admin profile.

### Setup

There is a new **JS Addons** under **Setup** menu once the plugin is enabled. Click on it.

It will show supported Addons, and if their status.

Click on the one you want to configure. You need the Key, Tag, URL or whatever code it is embedded on the JavaScript snippet.

Note: Setting up every service is out of the scope of this . Each of them has its own support; you can contact to get the codes or learn how to use them.

#### Metricool

In order to use it, create a new brand and choose it. Click on Web/Blog and them on the Connect Web button.

Choose Tracking pixel. There is an html code like this one:

```
<img src="https://tracker.metricool.com/c3po.jpg?hash=XXXXXXXXXXXXXXXXXXXXXXXXXXXXX"/>
```

Copy the hash, paste it to the Metricool form and activate it. You need to visit Metricool Real Time Dashboard in order to check if it is fully working.

#### Google Analytics

Google Analytics uses a Global Site Tag or **gtag** identified as GA_TRACKING_ID with this structure: 

**UA-XXXXXXXX-X**.

Copy it, paste it to the Google Analytics form and activate it. You need to visit Google Analytics in order to check if it is fully working.

Realtime report will show your current users.

#### Tawk.to

Login to your tawk.to dashboard. Click on Administration, and choose the property you want to configure. Select Channels > Chat Widget.

On Direct Chat Link there is a URL with this structure tawk.to/chat/**XXXXXXXXXXXXXXXXXXXXXXXX/XXXXXXX**

Copy the string after  tawk.to/chat/ and paste it to the Tawk.to form. Activate it. The Tawk.to widget will appear on the bottom right corner of GLPI.

## Adding new Services

We are glad to support new services. Please open an issue and give us all the needed information, to be added to next release.

## Support this plugin

By registering using our referral link, you are supporting the development of this plugin.

Please use this links to:

- Get a free chat for your website: [Tawk.to](https://www.tawk.to/?pid=snaotzu)
- Get free comprehensible analytics for your website and Social Networks: [Metricool](http://mtr.cool/yfuhbk)
